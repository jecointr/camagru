<?php
require_once __DIR__ . '/../models/Gallery.php';

class GalleryController {

    public function index() {
        $galleryModel = new Gallery();
        
        // --- Pagination ---
        $limit = 6; // 5 minimum requis par le sujet
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $images = $galleryModel->getImages($limit, $offset);
        $totalImages = $galleryModel->countImages();
        $totalPages = ceil($totalImages / $limit);

        // --- Pré-chargement des données (Likes/Comments) ---
        // Pour éviter de faire 100 requêtes dans la vue, on prépare les données ici
        foreach ($images as &$img) {
            $img['likes'] = $galleryModel->getLikeCount($img['id']);
            $img['comments'] = $galleryModel->getComments($img['id']);
        }

        require __DIR__ . '/../views/gallery.php';
    }

    public function like() {
        if (!isset($_SESSION['user_id'])) header('Location: /login');
        
        if (isset($_POST['image_id'])) {
            $model = new Gallery();
            $model->toggleLike($_SESSION['user_id'], $_POST['image_id']);
        }
        // Retour à la page précédente (simple)
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    public function comment() {
        if (!isset($_SESSION['user_id'])) header('Location: /login');

        if (isset($_POST['image_id']) && !empty($_POST['comment'])) {
            $model = new Gallery();
            $comment = htmlspecialchars($_POST['comment']); // Sécurité XSS !
            $imageId = $_POST['image_id'];

            if ($model->addComment($_SESSION['user_id'], $imageId, $comment)) {
                // --- Notification Mail (Mandatory) ---
                $owner = $model->getImageOwner($imageId);
                if ($owner && $owner['notification_active']) {
                    $to = $owner['email'];
                    $subject = "Nouveau commentaire sur votre photo Camagru";
                    $msg = "Quelqu'un a commenté votre photo !";
                    // mail($to, $subject, $msg); // Décommenter pour production
                    file_put_contents('php://stderr', "Mail envoyé à $to\n");
                }
            }
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}
?>
