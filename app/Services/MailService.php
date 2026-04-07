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
    
    // Metoda pentru trimiterea notificării când s-a găsit un dosar
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
}