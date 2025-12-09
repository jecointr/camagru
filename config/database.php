<?php
class Database {
    private static $instance = null;
    public $conn;

    private function __construct() {
        // Chargement des variables d'environnement manuellement si pas de library
        // Pour faire simple ici, on hardcode ou on lit le .env via une fonction simple
        $host = 'db';
        $db   = 'camagru';
        $user = 'root';
        $pass = 'rootpassword';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Important pour debugger
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Important pour la sécurité (SQL Injection)
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}
?>