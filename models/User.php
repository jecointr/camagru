<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function userExists($username, $email) {
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email]);
        return $stmt->fetch();
    }

    public function create($username, $email, $password, $token) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $default_pic = 'default_avatar.png';

        $sql = "INSERT INTO users (username, email, password, token, profile_pic) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([$username, $email, $hashed_password, $token, $default_pic]);
        } catch (PDOException $e) {
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
            return $user;
        }
        return false;
    }

    public function verifyAccount($token) {
        $sql = "UPDATE users SET is_verified = 1, token = NULL WHERE token = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }


    public function setResetToken($email) {
        $check = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if (!$check->fetch()) return false;

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
        $sql = "SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function resetPassword($token, $newPassword) {
        $user = $this->verifyResetToken($token);

        if ($user) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$hash, $user['id']]);
        }
        return false;
    }


    public function updateAvatar($userId, $filename) {
        $stmt = $this->db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        return $stmt->execute([$filename, $userId]);
    }

    public function update($id, $username, $email, $password = null) {
        $sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username, $email, $id]);
        if ($stmt->fetch()) return "EXISTS";

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

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT username, email, notification_active, profile_pic FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>