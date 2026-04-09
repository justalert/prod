<?php
// app/router.php

require_once BASE_PATH . 'Core/Router.php';
require_once BASE_PATH . 'Core/View.php';

$router = new Router();

// ==== DEFINIREA RUTELOR ==== //

// Pagina principală
$router->get('/', 'HomeController@index');

// Procesul de abonare (Formular)
$router->post('/api/subscribe', 'AlertController@store');

// Verificarea link-ului din email (Magic Link)
$router->get('/verify', 'AuthController@verify');

// Schimbarea limbii
$router->get('/change-language', 'LanguageController@change');

// Rute de Autentificare și Cont
$router->get('/login', 'AuthController@showLogin');
$router->post('/login/send', 'AuthController@sendLoginLink');
$router->get('/logout', 'AuthController@logout');

// Pagini Statice (Legal)
$router->get('/termeni-si-conditii', function() {
    View::render('pages/legal/terms', ['pageTitle' => translate('legal.terms.title')]);
});

// Politica de Confidențialitate
$router->get('/politica-confidentialitate', function() {
    View::render('pages/legal/privacy', ['pageTitle' => translate('legal.privacy.title')]);
});

// Politica de Cookie-uri
$router->get('/politica-de-cookies', function() {
    View::render('pages/legal/cookies', ['pageTitle' => translate('legal.cookies.title')]);
});

// Pagina de Transparență Tehnică
$router->get('/transparenta', function() {
    View::render('pages/about/transparenta', ['pageTitle' => translate('nav.transparency', 'Transparență & Arhitectură Tehnică')]);
});

// Pagina de Contact
$router->get('/contact', 'ContactController@index');
$router->post('/contact/send', 'ContactController@send');

// Rute pentru Dashboard
$router->get('/dashboard', 'DashboardController@index');
$router->post('/dashboard/toggle-ai', 'DashboardController@toggleAi');
$router->post('/dashboard/alert/toggle-status', 'DashboardController@toggleAlertStatus');
$router->post('/dashboard/alert/edit', 'DashboardController@editAlert');
$router->post('/dashboard/alert/delete', 'DashboardController@deleteAlert');

$router->post('/dashboard/alert/create', 'DashboardController@createAlert');

// Funcționalități GDPR
$router->get('/dashboard/export-data', 'DashboardController@exportData');
$router->post('/dashboard/clear-history', 'DashboardController@clearHistory');
$router->post('/dashboard/delete-account', 'DashboardController@deleteAccount');

// ==== PORNIREA APLICAȚIEI ==== //
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
