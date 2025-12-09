<?php
require_once __DIR__ . '/../config/database.php';

class Gallery {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Récupère les images avec pagination + info auteur
    public function getImages($limit, $offset) {
        $sql = "SELECT images.*, users.username 
                FROM images 
                JOIN users ON images.user_id = users.id 
                ORDER BY images.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        // PDO a parfois du mal avec LIMIT/OFFSET en string, on force le type INT
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Compte total pour la pagination
    public function countImages() {
        return $this->db->query("SELECT COUNT(*) FROM images")->fetchColumn();
    }

    // Gestion des Likes (Toggle : J'aime / Je n'aime plus)
    public function toggleLike($userId, $imageId) {
        // Vérifier si déjà liké
        $check = $this->db->prepare("SELECT id FROM likes WHERE user_id = ? AND image_id = ?");
        $check->execute([$userId, $imageId]);
        
        if ($check->rowCount() > 0) {
            // Suppression (Unlike)
            $stmt = $this->db->prepare("DELETE FROM likes WHERE user_id = ? AND image_id = ?");
            $stmt->execute([$userId, $imageId]);
            return "unliked";
        } else {
            // Ajout (Like)
            $stmt = $this->db->prepare("INSERT INTO likes (user_id, image_id) VALUES (?, ?)");
            $stmt->execute([$userId, $imageId]);
            return "liked";
        }
    }

    public function getLikeCount($imageId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE image_id = ?");
        $stmt->execute([$imageId]);
        return $stmt->fetchColumn();
    }

    // Gestion des Commentaires
    public function addComment($userId, $imageId, $comment) {
        $stmt = $this->db->prepare("INSERT INTO comments (user_id, image_id, comment) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $imageId, $comment]);
    }

    public function getComments($imageId) {
        $sql = "SELECT comments.*, users.username 
                FROM comments 
                JOIN users ON comments.user_id = users.id 
                WHERE image_id = ? 
                ORDER BY comments.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$imageId]);
        return $stmt->fetchAll();
    }

    // Utile pour la notification : récupérer l'email du propriétaire de l'image
    public function getImageOwner($imageId) {
        $sql = "SELECT users.email, users.notification_active 
                FROM images 
                JOIN users ON images.user_id = users.id 
                WHERE images.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$imageId]);
        return $stmt->fetch();
    }
}
?>
