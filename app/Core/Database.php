<?php
// app/Core/Database.php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // Citim configurația din fișierul .env aflat în rădăcina proiectului
        $envPath = dirname(BASE_PATH) . '/.env';
        $env = file_exists($envPath) ? parse_ini_file($envPath) : [];

        $host = $env['DB_HOST'] ?? 'localhost';
        $db   = $env['DB_NAME'] ?? 'apelero_justalert';
        $user = $env['DB_USER'] ?? 'root';
        $pass = $env['DB_PASS'] ?? '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            die("Eroare la conectarea cu baza de date. Te rugăm să revii mai târziu.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
