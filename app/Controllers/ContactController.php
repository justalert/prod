<?php
// app/Controllers/ContactController.php

require_once BASE_PATH . 'Services/MailService.php';

class ContactController {
    
    // Afișează pagina formularului
    public function index() {
        $numeComplet = '';

        if (isset($_SESSION['user_id'])) {
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT nume_familie, prenume FROM alerts WHERE user_id = ? ORDER BY id ASC LIMIT 1");
                $stmt->execute([$_SESSION['user_id']]);
                $alert = $stmt->fetch();

                if ($alert) {
                    $numeComplet = trim(($alert['nume_familie'] ?? '') . ' ' . ($alert['prenume'] ?? ''));
                }
            } catch (Exception $e) {
                error_log("Eroare la extragerea numelui pentru Contact: " . $e->getMessage());
            }
        }

        // --- NOU: GENERARE CAPTCHA MATEMATIC ---
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        $_SESSION['captcha_answer'] = $num1 + $num2;
        $captchaText = "{$num1} + {$num2}";
        // ----------------------------------------

        View::render('pages/contact/index', [
            'pageTitle' => translate('contact.title', 'Contact - JustAlert'),
            'numeComplet' => $numeComplet,
            'captchaText' => $captchaText // Trimitem textul către View
        ]);
    }

    // Procesează datele trimise
    public function send() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /contact");
            exit;
        }

        // Verificare CSRF Token
        if (!isset($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['error'] = translate('error.system', 'Eroare de securitate. Te rugăm să reîncarci pagina și să încerci din nou.');
            header("Location: /contact");
            exit;
        }

        // --- NOU: VALIDARE CAPTCHA MATEMATIC ---
        $userCaptcha = (int)($_POST['captcha'] ?? 0);
        $realCaptcha = (int)($_SESSION['captcha_answer'] ?? -1);

        if ($userCaptcha !== $realCaptcha) {
            $_SESSION['error'] = translate('error.invalid_captcha', 'Răspunsul la verificarea de securitate (matematică) este incorect.');
            header("Location: /contact");
            exit;
        }
        
        // Curățăm răspunsul din sesiune pentru a nu putea fi refolosit
        unset($_SESSION['captcha_answer']);
        // ----------------------------------------

        // Preluăm și curățăm datele
        $nume = trim($_POST['nume'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $mesaj = trim($_POST['mesaj'] ?? '');

        if (empty($nume) || empty($mesaj) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = translate('error.invalid_data', 'Te rugăm să completezi corect toate câmpurile.');
            header("Location: /contact");
            exit;
        }

        $trimis = MailService::sendContactMessage($email, $nume, $mesaj);

        if ($trimis) {
            $_SESSION['success'] = translate('contact.success', 'Mesajul tău a fost trimis cu succes. Îți vom răspunde în cel mai scurt timp posibil.');
        } else {
            $_SESSION['error'] = translate('error.system', 'A apărut o eroare la trimiterea mesajului. Te rugăm să încerci mai târziu.');
        }

        header("Location: /contact");
        exit;
    }
}
