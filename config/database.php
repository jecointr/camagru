<?php
class Database {
    private static $instance = null;
    public $conn;

    private function __construct() {
        // On aligne ces infos avec ton docker-compose.yml
        $host = 'db';
        $db   = 'camagru';
        $user = 'camagru_user';      // <--- Modifié (était root)
        $pass = 'user_password';     // <--- Modifié (était rootpassword)
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // Astuce : On affiche le message complet pour le debug si ça plante
            die("Erreur Connection BDD : " . $e->getMessage());
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