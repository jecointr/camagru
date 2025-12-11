<?php
class Database {
    private static $instance = null;
    public $conn;

    private function __construct() {
        // 1. Chargement manuel du .env (car pas de librairie autorisée)
        $this->loadEnv();

        // 2. Récupération des variables d'environnement
        $host = getenv('DB_HOST');
        $db   = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASSWORD');
        $charset = 'utf8mb4';

        // Vérification basique pour éviter de planter salement si le .env manque
        if (!$host || !$db || !$user || !$pass) {
            die("Erreur : Fichier .env mal configuré ou introuvable.");
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
            die("Erreur Connection BDD : " . $e->getMessage());
        }
    }

    // Fonction privée pour parser le fichier .env
    private function loadEnv() {
        $envFile = __DIR__ . '/../.env'; // Chemin vers la racine
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Ignore les commentaires commençant par #
                if (strpos(trim($line), '#') === 0) continue;

                // Découpe la ligne au premier '='
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Définit la variable d'environnement et $_ENV
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
?>