<?php
// app/Core/Translator.php

class Translator {
    private static array $translations = [];
    private static string $currentLang = 'ro'; // Default

    // Interogăm baza de date o singură dată per request
    public static function init(string $langCode = 'ro') {
        self::$currentLang = $langCode;
        $db = Database::getInstance();
        
        $stmt = $db->prepare("SELECT translation_key, translation_value FROM translations WHERE lang_code = ?");
        $stmt->execute([$langCode]);
        
        // PDO::FETCH_KEY_PAIR returnează un array de tip ['home.title' => 'Titlu']
        self::$translations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }

    // Extragem traducerea
    public static function get(string $key, string $default = '') {
        return self::$translations[$key] ?? ($default ?: $key);
    }
}

// Funcție globală helper pentru a păstra View-urile foarte scurte și curate
function translate(string $key, string $default = '') {
    return Translator::get($key, $default);
}