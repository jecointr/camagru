<?php

class Database {
    private static $instance = null;
    public $conn;

    private function __construct() {
        if (!getenv('DB_HOST')) {
            $this->loadEnv();
        }

        $env = getenv('APP_ENV') ?: 'production'; 

        $host = getenv('DB_HOST');
        $db   = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASSWORD');
        $charset = 'utf8mb4';

        if (!$host || !$db || !$user || !$pass) {
            error_log("CRITICAL: Variables d'environnement BDD manquantes.");
            
            if ($env === 'development') {
                die("Erreur Config : Variables manquantes (voir logs).");
            } else {
                header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
                die("Une erreur interne est survenue.");
            }
        }

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            
            error_log("Database Connection Error: " . $e->getMessage());

            if ($env === 'development') {
                die("Erreur SQL (Dev mode): " . $e->getMessage());
            } else {
                header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
                die("<h1>Service temporairement indisponible</h1><p>Nous rencontrons un problème technique. Veuillez réessayer plus tard.</p>");
            }
        }
    }

    private function loadEnv() {
        $envFile = __DIR__ . '/../.env'; 
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}