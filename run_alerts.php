<?php
// run_alerts.php

// 1. Definim calea de bază a aplicației
define('BASE_PATH', __DIR__ . '/app/');

// 2. Citim variabilele de mediu (.env)
$envPath = __DIR__ . '/.env';
$env = file_exists($envPath) ? parse_ini_file($envPath) : [];

// 3. Setăm cheia secretă HMAC necesară pentru serviciul de alerte
if (!empty($env['JUSTALERT_HMAC_SECRET'])) {
    define('JUSTALERT_HMAC_SECRET', $env['JUSTALERT_HMAC_SECRET']);
} else {
    die("Eroare: Trebuie să definești JUSTALERT_HMAC_SECRET în fișierul .env\n");
}

// 4. Încărcăm fișierele esențiale
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once BASE_PATH . 'Core/Database.php';
require_once BASE_PATH . 'Core/Translator.php';

// Pentru cron, setăm forțat limba pe Română ca să genereze mailurile corect
$_SESSION['lang'] = 'ro';
Translator::init('ro');

// 5. Încărcăm serviciul de notificări
require_once BASE_PATH . 'Services/AlertNotificationService.php';

// ==========================================
// RULAREA EFECTIVĂ
// ==========================================

echo "Pornesc verificarea dosarelor în Just.ro...\n";
echo "------------------------------------------\n";

try {
    $service = new AlertNotificationService();
    $rezumat = $service->run();
    
    echo "Verificare finalizată cu succes!\n\n";
    echo "📊 REZUMAT:\n";
    echo "==========================================\n";
    echo "Alerte totale active: " . $rezumat['alerts_total'] . "\n";
    echo "Alerte procesate cu succes: " . $rezumat['alerts_processed'] . "\n";
    echo "Alerte pentru care s-au găsit dosare noi/modificate: " . $rezumat['alerts_with_notifications'] . "\n";
    echo "Total emailuri expediate: " . $rezumat['notifications_sent'] . "\n";
    echo "Erori întâmpinate: " . $rezumat['errors'] . "\n";
    echo "==========================================\n";

} catch (Exception $e) {
    echo "❌ A apărut o eroare critică la rulare:\n";
    echo $e->getMessage() . "\n";
}