<?php
require_once ROOT . '/models/Gallery.php';

class GalleryController {

    public function index() {
        $galleryModel = new Gallery();
        
        // Pagination
        $limit = 6; // Images par page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $images = $galleryModel->getImages($limit, $offset);
        $totalImages = $galleryModel->countImages();
        $totalPages = ceil($totalImages / $limit);

        // On prépare les données supplémentaires (Likes, Comments) pour chaque image
        // (Note: C'est un peu "lourd" en requêtes, mais simple et efficace pour ce projet)
        foreach ($images as &$img) {
            $img['likes'] = $galleryModel->getLikeCount($img['id']);
            $img['comments'] = $galleryModel->getComments($img['id']);
        }

        require VIEWS . '/gallery.php';
    }

    public function like() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
        
        if (isset($_POST['image_id'])) {
            $model = new Gallery();
            $model->toggleLike($_SESSION['user_id'], $_POST['image_id']);
        }
        // Redirige vers la page précédente
        header('Location: ' . $_SERVER['HTTP_REFERER']); 
    }

    public function comment() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }

        if (isset($_POST['image_id']) && !empty($_POST['comment'])) {
            $model = new Gallery();
            $comment = htmlspecialchars($_POST['comment']);
            $imageId = $_POST['image_id'];

            if ($model->addComment($_SESSION['user_id'], $imageId, $comment)) {
                
                // --- NOTIFICATION EMAIL (MANDATORY) ---
                $owner = $model->getImageOwner($imageId);
                
                // Si l'auteur veut des notifs et que ce n'est pas lui-même qui commente
                if ($owner && $owner['notification_active'] && $owner['email']) {
                    $subject = "Nouveau commentaire sur Camagru";
                    $message = "Bonjour " . $owner['username'] . ",\n\nUne nouvelle personne a commenté votre photo : \n\n\"$comment\"\n\nConnectez-vous pour répondre !";
                    $headers = "From: no-reply@camagru.fr";
                    
                    mail($owner['email'], $subject, $message, $headers);
                }
            }
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    public function delete() {
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }

        if (isset($_POST['image_id'])) {
            $model = new Gallery();
            $model->deleteImage($_POST['image_id'], $_SESSION['user_id']);
        }
        header('Location: /gallery');
    }
}
?>