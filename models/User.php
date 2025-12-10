<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Vérifier si un utilisateur existe déjà (pour l'inscription)
    public function userExists($username, $email) {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        return $stmt->fetch();
    }

    // Créer un nouvel utilisateur
    public function create($username, $email, $password, $token) {
        // Hashage du mot de passe (Sécurité Mandatory)
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([$username, $email, $hashed_password, $token]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Connexion utilisateur
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Vérification du hash
        if ($user && password_verify($password, $user['password'])) {
            // Vérification de l'email (Mandatory)
            if ($user['is_verified'] == 0) {
                return "NOT_VERIFIED";
            }
            return $user;
        }
        return false;
    }

    // Validation du compte par email
    public function verifyAccount($token) {
        $sql = "UPDATE users SET is_verified = 1, token = NULL WHERE token = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    // --- Fonctions pour Reset Password ---

    public function setTokenByEmail($email, $token) {
        $sql = "UPDATE users SET token = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token, $email]);
        return $stmt->rowCount() > 0;
    }

    public function resetPassword($token, $newPassword) {
        // Vérifie le token
        $sql = "SELECT id FROM users WHERE token = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            // Met à jour mdp et supprime le token
            $update = $this->db->prepare("UPDATE users SET password = ?, token = NULL WHERE id = ?");
            return $update->execute([$hash, $user['id']]);
        }
        return false;
    }

    // Mettre à jour les infos utilisateur
    public function update($id, $username, $email, $password = null) {
        // 1. On vérifie si username/email sont déjà pris par QUELQU'UN D'AUTRE
        $sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email, $id]);
        if ($stmt->fetch()) return "EXISTS";

        // 2. Construction de la requête dynamique
        if ($password) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
            $params = [$username, $email, $hash, $id];
        } else {
            $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            $params = [$username, $email, $id];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Récupérer un user par ID (pour pré-remplir le formulaire)
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT username, email, notification_active FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>