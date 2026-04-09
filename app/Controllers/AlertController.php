<?php
// app/Controllers/AuthController.php

require_once BASE_PATH . 'Services/MailService.php';

class AuthController {
    
    // 1. Arată pagina de Login
    public function showLogin() {
        // Dacă utilizatorul vine de pe un link expirat (?expired=1), setăm eroarea în sesiune
        if (isset($_GET['expired'])) {
            $_SESSION['error'] = translate('error.link_expired', 'Acest link a expirat. Te rugăm să soliciți unul nou.');
            header("Location: /login"); // Curățăm URL-ul
            exit;
        }

        View::render('pages/auth/login', ['pageTitle' => translate('nav.login', 'Autentificare')]);
    }

    // 2. Procesează cererea de trimitere a link-ului
    public function sendLoginLink() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /login");
            exit;
        }

        // Verificare CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['error'] = translate('error.system', 'Eroare de securitate. Te rugăm să reîncarci pagina și să încerci din nou.');
            header("Location: /login");
            exit;
        }

        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = translate('error.invalid_email', 'Adresă de email invalidă.');
            header("Location: /login");
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userId = $stmt->fetchColumn();

        // Securitate: Chiar dacă nu găsim emailul, spunem că l-am trimis 
        // pentru a nu permite "hackerilor" să afle ce adrese sunt înregistrate la noi.
        if ($userId) {
            $token = bin2hex(random_bytes(32)); 
            $tokenHash = hash('sha256', $token); 
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt = $db->prepare("INSERT INTO magic_links (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $tokenHash, $expiresAt]);

            // Trimitere email folosind MailService
            MailService::sendAccessLink($email, $token); 
        }

        $_SESSION['success'] = translate('success.magic_link_sent', 'Ți-am trimis un link de acces securizat pe email.');
        header("Location: /login");
        exit;
    }

    // 3. Deconectarea (Modificată pentru a păstra limba în interfață)
    public function logout() {
        // Memorăm limba curentă înainte de a distruge sesiunea
        $savedLang = $_SESSION['lang'] ?? 'ro';
        
        session_destroy();
        
        // Repornim sesiunea curată pentru a păstra preferința vizuală de limbă
        session_start();
        $_SESSION['lang'] = $savedLang;
        
        $_SESSION['success'] = translate('success.logged_out', 'Deconectare reușită.');
        header("Location: /");
        exit;
    }

    // 4. Verificarea linkului și declanșarea scanării inițiale
    public function verify() {
        $email = $_GET['email'] ?? '';
        $token = $_GET['token'] ?? '';

        if (empty($email) || empty($token)) {
            header("Location: /login?expired=1"); // Redirecționăm direct la login
            exit;
        }

        $db = Database::getInstance();
        $tokenHash = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');

        try {
            $db->beginTransaction();

            // Extragem datele linkului și limba utilizatorului
            $stmt = $db->prepare("
                SELECT ml.id, ml.user_id, u.lang 
                FROM magic_links ml
                JOIN users u ON ml.user_id = u.id
                WHERE u.email = ? AND ml.token_hash = ? AND ml.expires_at > ?
            ");
            $stmt->execute([$email, $tokenHash, $now]);
            $linkData = $stmt->fetch();

            if (!$linkData) {
                // Dacă link-ul e expirat sau folosit, trimitem pe login cu mesaj explicit
                header("Location: /login?expired=1");
                exit;
            }

            // --- NOU: Preluăm ID-urile alertelor CE URMEAZĂ a fi activate ---
            $stmt = $db->prepare("SELECT id FROM alerts WHERE user_id = ? AND status = 'pending_verification'");
            $stmt->execute([$linkData['user_id']]);
            $pendingAlerts = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Activăm alertele pending (dacă există)
            $stmt = $db->prepare("UPDATE alerts SET status = 'active' WHERE user_id = ? AND status = 'pending_verification'");
            $stmt->execute([$linkData['user_id']]);

            // Ștergem TOATE tokenurile acestui utilizator pentru a invalida orice link vechi
            $stmt = $db->prepare("DELETE FROM magic_links WHERE user_id = ?");
            $stmt->execute([$linkData['user_id']]);

            $db->commit();

            // --- NOU: Rulăm asincron în background scanarea inițială ---
            if (!empty($pendingAlerts)) {
                // Dacă pe serverul tău comanda `php` nu este recunoscută direct, 
                // poți schimba aici cu calea completă, de ex: '/usr/local/bin/php'
                $phpExecutable = 'php'; 
                $scriptPath = dirname(BASE_PATH) . '/run_initial_scan.php';
                
                foreach ($pendingAlerts as $alertId) {
                    // exec cu > /dev/null 2>&1 & asigură rularea în fundal
                    exec("$phpExecutable $scriptPath $alertId > /dev/null 2>&1 &");
                }
            }

            // Logăm utilizatorul
            $_SESSION['user_id'] = $linkData['user_id'];
            $_SESSION['user_email'] = $email;
            
            // Setăm limba din sesiune conform preferinței din baza de date
            if (!empty($linkData['lang'])) {
                $_SESSION['lang'] = $linkData['lang'];
            }

            // Afișăm un mesaj relevant utilizatorului
            if (!empty($pendingAlerts)) {
                $_SESSION['success'] = translate('success.verified_initial', 'Autentificare reușită! Căutăm primele dosare în fundal. Vei primi un email curând.');
            } else {
                $_SESSION['success'] = translate('success.verified', 'Autentificare reușită!');
            }
            
            // Redirectul corect către noul Dashboard
            header("Location: /dashboard"); 
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Eroare Auth Verify: " . $e->getMessage());
            $_SESSION['error'] = translate('error.system', 'A apărut o eroare de sistem. Te rugăm să încerci din nou.');
            header("Location: /");
            exit;
        }
    }
}
