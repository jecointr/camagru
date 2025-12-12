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
        
        // On définit une image par défaut si non spécifiée
        $default_pic = 'default_avatar.png';

        $sql = "INSERT INTO users (username, email, password, token, profile_pic) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([$username, $email, $hashed_password, $token, $default_pic]);
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

    // --- Fonctions Sécurisées pour Reset Password (NOUVEAU) ---

    public function setResetToken($email) {
        // 1. Vérifie si l'email existe
        $check = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if (!$check->fetch()) return false;

        // 2. Génère token + expiration (1 heure)
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); 

        $sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([$token, $expires, $email])) {
            return $token;
        }
        return false;
    }

    public function verifyResetToken($token) {
        // Vérifie si le token existe ET s'il n'est pas expiré
        $sql = "SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function resetPassword($token, $newPassword) {
        $user = $this->verifyResetToken($token);

        if ($user) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            // Met à jour le MDP et supprime le token de reset (usage unique)
            $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$hash, $user['id']]);
        }
        return false;
    }

    // --- Gestion Photo de Profil (NOUVEAU) ---

    public function updateAvatar($userId, $filename) {
        $stmt = $this->db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        return $stmt->execute([$filename, $userId]);
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
        // Ajout de profile_pic dans la sélection
        $stmt = $this->db->prepare("SELECT username, email, notification_active, profile_pic FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>