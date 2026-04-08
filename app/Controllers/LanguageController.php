<?php
// app/Controllers/LanguageController.php

class LanguageController {
    public function change() {
        // Preluăm limba din URL, default 'ro'
        $lang = $_GET['lang'] ?? 'ro';
        
        // Listă albă (Whitelist) pentru securitate - acceptăm doar ce avem în baza de date
        $allowedLanguages = ['ro', 'en'];

        if (in_array($lang, $allowedLanguages)) {
            // 1. Salvăm în sesiune (pentru toți utilizatorii, inclusiv cei nelogați)
            $_SESSION['lang'] = $lang;

            // 2. DACĂ utilizatorul este logat, salvăm preferința și în baza de date
            if (isset($_SESSION['user_id'])) {
                try {
                    $db = Database::getInstance();
                    $stmt = $db->prepare("UPDATE users SET lang = ? WHERE id = ?");
                    $stmt->execute([$lang, $_SESSION['user_id']]);
                } catch (Exception $e) {
                    // Dacă baza de date pică, măcar i se schimbă limba temporar în sesiune
                    error_log("Eroare la salvarea limbii în DB: " . $e->getMessage());
                }
            }
        }

        // Căutăm pagina de unde a venit utilizatorul (Referer)
        // Dacă nu o găsim (ex: a accesat link-ul direct), default este home '/'
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        
        // --- PROTECȚIE SECURITATE: Open Redirect ---
        // Ne asigurăm că redirect-ul se face exclusiv către pagini de pe domeniul nostru.
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $refererHost = parse_url($referer, PHP_URL_HOST);

        // Dacă hostul de referință există (nu este null) dar este diferit de domeniul platformei,
        // îl trimitem în siguranță pe pagina principală (Acasă).
        if ($refererHost !== null && $refererHost !== $host) {
            $referer = '/';
        }
        // -------------------------------------------
        
        header("Location: " . $referer);
        exit(); // Oprim execuția scriptului după redirect
    }
}
