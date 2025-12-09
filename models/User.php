<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($username, $email, $password, $token) {
        // Le hashage est OBLIGATOIRE pour la sécurité
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([$username, $email, $hashed_password, $token]);
        } catch (PDOException $e) {
            // Gestion des doublons (username ou email déjà pris)
            return false;
        }
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 0) {
                return "NOT_VERIFIED";
            }
            return $user; // Retourne toutes les infos de l'utilisateur
        }
        return false;
    }

    public function verifyAccount($token) {
        $sql = "UPDATE users SET is_verified = 1, token = NULL WHERE token = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }
    
    // Pour vérifier si un email/user existe déjà (utile pour les formulaires)
    public function userExists($username, $email) {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        return $stmt->fetch();
    }
}
?>
