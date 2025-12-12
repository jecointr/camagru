<?php
require_once ROOT . '/config/database.php';

class Gallery {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // On a viré $lastTimestamp. On ne filtre que par ID.
    public function getImages($limit, $lastId = null) {
        $sql = "SELECT images.*, users.username 
                FROM images 
                JOIN users ON images.user_id = users.id ";

        // LOGIQUE SIMPLE ET BETON :
        // On veut juste les images avec un ID plus petit que le dernier affiché
        if ($lastId) {
            $sql .= "WHERE images.id < :lastId ";
        }

        // Tri par ID décroissant (Le plus grand ID est forcément le plus récent)
        $sql .= "ORDER BY images.id DESC 
                 LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        
        if ($lastId) {
            $stmt->bindValue(':lastId', (int) $lastId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countImages() {
        return $this->db->query("SELECT COUNT(*) FROM images")->fetchColumn();
    }

    public function toggleLike($userId, $imageId) {
        $check = $this->db->prepare("SELECT id FROM likes WHERE user_id = ? AND image_id = ?");
        $check->execute([$userId, $imageId]);
        if ($check->rowCount() > 0) {
            $stmt = $this->db->prepare("DELETE FROM likes WHERE user_id = ? AND image_id = ?");
            $stmt->execute([$userId, $imageId]);
            return 'unliked';
        } else {
            $stmt = $this->db->prepare("INSERT INTO likes (user_id, image_id) VALUES (?, ?)");
            $stmt->execute([$userId, $imageId]);
            return 'liked';
        }
    }

    public function getLikeCount($imageId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE image_id = ?");
        $stmt->execute([$imageId]);
        return $stmt->fetchColumn();
    }

    public function addComment($userId, $imageId, $content) {
        $stmt = $this->db->prepare("INSERT INTO comments (user_id, image_id, comment) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $imageId, $content]);
    }

    public function getComments($imageId) {
        $sql = "SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE image_id = ? ORDER BY comments.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$imageId]);
        return $stmt->fetchAll();
    }

    public function getImageOwner($imageId) {
        $sql = "SELECT users.email, users.notification_active, users.username FROM images JOIN users ON images.user_id = users.id WHERE images.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$imageId]);
        return $stmt->fetch();
    }

    public function deleteImage($imageId, $userId) {
        $check = $this->db->prepare("SELECT filename FROM images WHERE id = ? AND user_id = ?");
        $check->execute([$imageId, $userId]);
        $img = $check->fetch();

        if ($img) {
            $path = ROOT . '/public/uploads/' . $img['filename'];
            if (file_exists($path)) unlink($path);
            $del = $this->db->prepare("DELETE FROM images WHERE id = ?");
            return $del->execute([$imageId]);
        }
        return false;
    }
}
?>