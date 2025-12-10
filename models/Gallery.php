<?php
require_once ROOT . '/config/database.php';

class Gallery {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Récupérer les images avec Pagination + Info Auteur
    public function getImages($limit, $offset) {
        // On sélectionne l'image, le nom de l'auteur, et on compte les likes
        // (Note: Pour faire simple en PHP natif, on peut compter les likes séparément ou via sous-requête)
        $sql = "SELECT images.*, users.username 
                FROM images 
                JOIN users ON images.user_id = users.id 
                ORDER BY images.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Compter le nombre total d'images (pour calculer le nombre de pages)
    public function countImages() {
        return $this->db->query("SELECT COUNT(*) FROM images")->fetchColumn();
    }

    // Gérer les Likes
    public function toggleLike($userId, $imageId) {
        // Vérifie si déjà liké
        $check = $this->db->prepare("SELECT id FROM likes WHERE user_id = ? AND image_id = ?");
        $check->execute([$userId, $imageId]);
        
        if ($check->rowCount() > 0) {
            $stmt = $this->db->prepare("DELETE FROM likes WHERE user_id = ? AND image_id = ?");
            $stmt->execute([$userId, $imageId]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO likes (user_id, image_id) VALUES (?, ?)");
            $stmt->execute([$userId, $imageId]);
        }
    }

    public function getLikeCount($imageId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE image_id = ?");
        $stmt->execute([$imageId]);
        return $stmt->fetchColumn();
    }

    // Gérer les Commentaires
    public function addComment($userId, $imageId, $content) {
        $stmt = $this->db->prepare("INSERT INTO comments (user_id, image_id, comment) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $imageId, $content]);
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

    // Récupérer l'email de l'auteur d'une image (pour la notif)
    public function getImageOwner($imageId) {
        $sql = "SELECT users.email, users.notification_active, users.username 
                FROM images 
                JOIN users ON images.user_id = users.id 
                WHERE images.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$imageId]);
        return $stmt->fetch();
    }

    // Supprimer une image (Seulement si on est proprio)
    public function deleteImage($imageId, $userId) {
        // 1. Vérifier ownership
        $check = $this->db->prepare("SELECT image_path FROM images WHERE id = ? AND user_id = ?");
        $check->execute([$imageId, $userId]);
        $img = $check->fetch();

        if ($img) {
            // 2. Supprimer fichier
            $path = ROOT . '/public/uploads/' . $img['image_path'];
            if (file_exists($path)) unlink($path);

            // 3. Supprimer BDD (Cascade supprimera likes/comments)
            $del = $this->db->prepare("DELETE FROM images WHERE id = ?");
            return $del->execute([$imageId]);
        }
        return false;
    }
}
?>