<?php
// app/Services/MailService.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailService {
    
    // Inițializăm PHPMailer cu setările tale de server SMTP extrase din .env
    private static function getMailer(): PHPMailer {
        $mail = new PHPMailer(true); // 'true' aruncă excepții la erori
        
        // Citim configurația din fișierul .env
        $envPath = dirname(BASE_PATH) . '/.env';
        $env = file_exists($envPath) ? parse_ini_file($envPath) : [];
        
        // Setări Server SMTP
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST'] ?? 'mail.justalert.eu';
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['SMTP_USER'] ?? 'no-reply@justalert.eu';
        $mail->Password   = $env['SMTP_PASS'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $env['SMTP_PORT'] ?? 465;
        
        // Setări Expeditor
        $mail->setFrom($env['SMTP_USER'] ?? 'no-reply@justalert.eu', 'JustAlert');
        $mail->CharSet = 'UTF-8'; // Suport diacritice în titlu și conținut

        return $mail;
    }

    // 1. Trimiterea link-ului de CONFIRMARE a unei noi alerte
    public static function sendMagicLink(string $toEmail, string $token): bool {
        try {
            $mail = self::getMailer();
            $mail->addAddress($toEmail);

            // Generăm link-ul cu parametrii vizibili (folosim urlencode pt siguranță)
            $verifyLink = "https://justalert.eu/verify?email=" . urlencode($toEmail) . "&token=" . $token;

            // Folosim dicționarul de traduceri pentru conținut
            $mail->Subject = translate('email.magic_link.subject', 'JustAlert - Confirmare Alerta');
            
            $bodyHTML = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto;'>
                    <h2 style='color: #0d6efd;'>" . translate('email.magic_link.title', 'Confirmare Setare Alertă') . "</h2>
                    <p>" . translate('email.magic_link.body', 'Ai solicitat setarea unei alerte pe platforma JustAlert. Pentru a activa și valida această adresă de email, te rugăm să dai click pe butonul de mai jos (link-ul expiră în 15 minute):') . "</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$verifyLink}' style='display: inline-block; padding: 12px 24px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;'>" . translate('email.magic_link.button', 'Activează Alerta') . "</a>
                    </p>
                    <p>" . translate('email.magic_link.footer', 'Dacă nu tu ai solicitat acest lucru, poți ignora acest mesaj. Nicio alertă nu va fi activată.') . "</p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #777;'>Link direct: {$verifyLink}</p>
                </div>
            ";

            $mail->isHTML(true);
            $mail->Body    = $bodyHTML;
            // O versiune plain text curată, în caz că furnizorul blochează HTML-ul
            $mail->AltBody = strip_tags(str_replace(['<br>', '<h2>', '<p>'], ["\n", "\n\n", "\n\n"], $bodyHTML));

            $mail->send();
            return true;
            
        } catch (PHPMailerException $e) {
            // Logăm eroarea SMTP fără să o arătăm direct vizitatorului
            error_log("Eroare trimitere email de confirmare către {$toEmail}: " . $mail->ErrorInfo);
            throw new Exception("Nu am putut trimite email-ul de confirmare.");
        }
    }

    // 2. Trimiterea link-ului de ACCES (Login) în contul existent
    public static function sendAccessLink(string $toEmail, string $token): bool {
        try {
            $mail = self::getMailer();
            $mail->addAddress($toEmail);
            
            // Link-ul duce tot către /verify, pentru că logica din AuthController se ocupă de validare și logare
            $verifyLink = "https://justalert.eu/verify?email=" . urlencode($toEmail) . "&token=" . $token;

            $mail->Subject = translate('email.login.title', 'Acces Cont JustAlert');
            
            $bodyHTML = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto;'>
                    <h2 style='color: #0d6efd;'>" . translate('email.login.title', 'Acces Cont JustAlert') . "</h2>
                    <p>" . translate('email.login.body', 'Apasă pe butonul de mai jos pentru a accesa contul tău. Link-ul este valabil 15 minute.') . "</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$verifyLink}' style='display: inline-block; padding: 12px 24px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;'>" . translate('email.login.button', 'Intră în cont') . "</a>
                    </p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #777;'>Link direct: {$verifyLink}</p>
                </div>
            ";

            $mail->isHTML(true);
            $mail->Body    = $bodyHTML;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<h2>', '<p>'], ["\n", "\n\n", "\n\n"], $bodyHTML));

            $mail->send();
            return true;
            
        } catch (PHPMailerException $e) {
            error_log("Eroare trimitere email de login către {$toEmail}: " . $mail->ErrorInfo);
            throw new Exception("Nu am putut trimite email-ul de acces.");
        }
    }
    
    // 3. Metoda pentru trimiterea notificării când s-a găsit un dosar NOU sau MODIFICAT
    public static function sendAlertNotification(string $toEmail, string $numeCautat, array $dosareNoi): bool {
        try {
            $mail = self::getMailer();
            $mail->addAddress($toEmail);

            $mail->Subject = translate('email.alert.subject');
            
            // Construim lista de dosare HTML
            $listaDosareHtml = "";
            foreach ($dosareNoi as $dosar) {
                // Extragem datele din obiectul returnat de API
                $numar = htmlspecialchars($dosar->numar ?? 'N/A');
                $data = isset($dosar->data) ? date('d.m.Y', strtotime($dosar->data)) : 'N/A';
                $institutie = htmlspecialchars($dosar->institutie ?? 'N/A');
                $obiect = htmlspecialchars($dosar->obiect ?? 'N/A');
                $categorie = htmlspecialchars($dosar->categorieCazNume ?? 'N/A');

                $listaDosareHtml .= "
                <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #dc3545; margin-bottom: 10px;'>
                    <h3 style='margin-top:0; color: #dc3545;'>Dosar: {$numar}</h3>
                    <p style='margin: 5px 0;'><strong>Data:</strong> {$data}</p>
                    <p style='margin: 5px 0;'><strong>Instanța:</strong> {$institutie}</p>
                    <p style='margin: 5px 0;'><strong>Obiect:</strong> {$obiect}</p>
                    <p style='margin: 5px 0;'><strong>Categorie:</strong> {$categorie}</p>
                </div>";
            }

            $dashboardLink = "https://justalert.eu/dashboard";

            $bodyHTML = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto;'>
                    <h2 style='color: #dc3545;'>" . translate('email.alert.title') . "</h2>
                    <p>" . translate('email.alert.body1') . " <strong>{$numeCautat}</strong>.</p>
                    <p>" . translate('email.alert.body2') . "</p>
                    
                    {$listaDosareHtml}

                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$dashboardLink}' style='display: inline-block; padding: 12px 24px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;'>" . translate('email.alert.btn_dashboard') . "</a>
                    </p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #777;'>" . translate('email.alert.footer') . "</p>
                </div>
            ";

            $mail->isHTML(true);
            $mail->Body = $bodyHTML;
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Eroare trimitere alertă dosar către {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    // 4. NOU: Metodă pentru trimiterea raportului inițial de Bun Venit
    public static function sendWelcomeNotification(string $toEmail, string $numeCautat, array $dosareNoi): bool {
        try {
            $mail = self::getMailer();
            $mail->addAddress($toEmail);

            $mail->Subject = translate('email.welcome.subject', 'JustAlert - Cont activat și primele dosare găsite!');
            
            // Construim lista de dosare HTML
            $listaDosareHtml = "";
            foreach ($dosareNoi as $dosar) {
                $numar = htmlspecialchars($dosar->numar ?? 'N/A');
                $data = isset($dosar->data) ? date('d.m.Y', strtotime($dosar->data)) : 'N/A';
                $institutie = htmlspecialchars($dosar->institutie ?? 'N/A');
                $obiect = htmlspecialchars($dosar->obiect ?? 'N/A');

                $listaDosareHtml .= "
                <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #198754; margin-bottom: 10px;'>
                    <h3 style='margin-top:0; color: #198754;'>Dosar: {$numar}</h3>
                    <p style='margin: 5px 0;'><strong>Data:</strong> {$data}</p>
                    <p style='margin: 5px 0;'><strong>Instanța:</strong> {$institutie}</p>
                    <p style='margin: 5px 0;'><strong>Obiect:</strong> {$obiect}</p>
                </div>";
            }

            $dashboardLink = "https://justalert.eu/dashboard";

            $bodyHTML = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto;'>
                    <h2 style='color: #198754;'>" . translate('email.welcome.title', 'Bun venit pe JustAlert!') . "</h2>
                    <p>" . translate('email.welcome.body1', 'Contul tău a fost validat. Am analizat portalul instanțelor pentru numele') . " <strong>{$numeCautat}</strong>.</p>
                    <p>" . translate('email.welcome.body2', 'Acestea sunt dosarele pe care le-am găsit în acest moment (aceasta este lista de bază). De acum înainte, te vom notifica <strong>doar când apar dosare noi sau termene noi</strong> la dosarele existente:') . "</p>
                    
                    {$listaDosareHtml}

                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$dashboardLink}' style='display: inline-block; padding: 12px 24px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold;'>" . translate('email.alert.btn_dashboard', 'Vezi Panoul Meu') . "</a>
                    </p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #777;'>" . translate('email.alert.footer', 'Poți dezactiva aceste alerte oricând din contul tău.') . "</p>
                </div>
            ";

            $mail->isHTML(true);
            $mail->Body = $bodyHTML;
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Eroare trimitere email welcome către {$toEmail}: " . $e->getMessage());
            return false;
        }
    }
    
    // 5. Metodă pentru trimiterea formularului de contact către Admin
    public static function sendContactMessage(string $fromEmail, string $name, string $message): bool {
        try {
            $mail = self::getMailer();
            
            // Citim adresa de destinație pentru contact din .env
            $envPath = dirname(BASE_PATH) . '/.env';
            $env = file_exists($envPath) ? parse_ini_file($envPath) : [];
            
            // Căutăm CONTACT_EMAIL. Dacă nu e definită, lăsăm un fallback clar (ex: contact@justalert.eu)
            $adminEmail = $env['CONTACT_EMAIL'] ?? 'contact@justalert.eu';

            $mail->addAddress($adminEmail); // Trimitem mesajul CĂTRE adresa de suport/admin
            $mail->addReplyTo($fromEmail, $name); // Setăm Reply-To către utilizatorul care a completat formularul

            $mail->Subject = "Formular Contact JustAlert - " . $name;
            
            // Protejăm conținutul împotriva injecțiilor XSS în clientul de mail
            $safeMessage = nl2br(htmlspecialchars($message));
            $safeName = htmlspecialchars($name);
            $safeEmail = htmlspecialchars($fromEmail);

            $bodyHTML = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px;'>
                    <h2 style='color: #0d6efd;'>Mesaj nou de pe site (Contact)</h2>
                    <p><strong>De la:</strong> {$safeName} ({$safeEmail})</p>
                    <p><strong>Data:</strong> " . date('d.m.Y H:i:s') . "</p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p><strong>Mesaj:</strong></p>
                    <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0d6efd;'>
                        {$safeMessage}
                    </div>
                </div>
            ";

            $mail->isHTML(true);
            $mail->Body = $bodyHTML;
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Eroare trimitere mesaj contact de la {$fromEmail}: " . $e->getMessage());
            return false;
        }
    }
}
