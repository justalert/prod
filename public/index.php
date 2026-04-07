<?php
// public/index.php

// 1. Definim calea de bază a aplicației
define('BASE_PATH', dirname(__DIR__) . '/app/');

// 2. Citim variabilele de mediu (.env) pentru a ști în ce stadiu suntem
$envPath = dirname(__DIR__) . '/.env';
$env = file_exists($envPath) ? parse_ini_file($envPath) : [];
$appEnv = $env['APP_ENV'] ?? 'production';

// 3. Configurări pentru afișarea erorilor
if ($appEnv === 'development') {
    // Afișăm erorile doar când dezvoltăm
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // În producție (live), ascundem erorile de utilizator, dar le logăm în fișierele serverului
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}

// 4. Pornim sesiunile globale
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Generăm token-ul CSRF pentru securitatea formularelor dacă nu există deja
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 5. Încărcăm pachetele instalate prin Composer
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// 6. Încărcăm core-ul aplicației
require_once BASE_PATH . 'Core/Database.php';
require_once BASE_PATH . 'Core/Translator.php';

// 7. Inițializăm sistemul de traduceri
$lang = $_SESSION['lang'] ?? 'ro';
Translator::init($lang);

// 8. Invocăm Router-ul
require_once BASE_PATH . 'router.php';
