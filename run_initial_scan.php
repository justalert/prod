<?php
// run_initial_scan.php

// Măsură de securitate: acest script nu poate fi accesat din browser, doar intern (CLI)
if (php_sapi_name() !== 'cli') {
    die("Acest script poate fi rulat doar din linia de comanda.\n");
}

// 1. Definim calea de bază a aplicației
define('BASE_PATH', __DIR__ . '/app/');

// 2. Citim variabilele de mediu (.env)
$envPath = __DIR__ . '/.env';
$env = file_exists($envPath) ? parse_ini_file($envPath) : [];

if (!empty($env['JUSTALERT_HMAC_SECRET'])) {
    define('JUSTALERT_HMAC_SECRET', $env['JUSTALERT_HMAC_SECRET']);
} else {
    die("Eroare: Trebuie să definești JUSTALERT_HMAC_SECRET în fișierul .env\n");
}

// 3. Încărcăm fișierele esențiale
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once BASE_PATH . 'Core/Database.php';
require_once BASE_PATH . 'Core/Translator.php';
require_once BASE_PATH . 'Services/AlertNotificationService.php';

// Preluăm ID-ul alertei trimis ca argument din AuthController
$alertId = (int)($argv[1] ?? 0);

if ($alertId > 0) {
    try {
        $service = new AlertNotificationService();
        // Rulăm procesarea cu flag-ul "true" pentru $isInitial (trimite emailul de Welcome)
        $service->runForAlertId($alertId, true);
        echo "Scanare initiala finalizata pentru alerta: {$alertId}\n";
    } catch (Exception $e) {
        error_log("Eroare la rularea scanarii initiale (Alert ID {$alertId}): " . $e->getMessage());
    }
}
