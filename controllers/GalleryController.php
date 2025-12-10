<?php
require_once ROOT . '/models/Gallery.php';

class GalleryController {

    public function index() {
        $galleryModel = new Gallery();
        
        $limit = 6;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $images = $galleryModel->getImages($limit, $offset);
        
        // Enrichissement des données
        foreach ($images as &$img) {
            $img['likes'] = $galleryModel->getLikeCount($img['id']);
            $img['comments'] = $galleryModel->getComments($img['id']);
        }
        unset($img); // Break reference
        
        // --- GESTION AJAX ---
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'images' => $images,
                // S'il y a autant d'images que la limite, on suppose qu'il y a une page suivante
                'next_page' => (count($images) === $limit) ? $page + 1 : null
            ]);
            exit;
        }

        // --- AFFICHAGE STANDARD ---
        $totalImages = $galleryModel->countImages();
        $totalPages = ceil($totalImages / $limit);
        
        require VIEWS . '/gallery.php';
    }

    public function like() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']); exit;
        }

        if (isset($_POST['image_id'])) {
            $model = new Gallery();
            $status = $model->toggleLike($_SESSION['user_id'], $_POST['image_id']);
            $newCount = $model->getLikeCount($_POST['image_id']);
            echo json_encode(['success' => true, 'status' => $status, 'new_count' => $newCount]); exit;
        }
        echo json_encode(['success' => false]); exit;
    }

    public function comment() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']); exit;
        }

        if (isset($_POST['image_id']) && !empty($_POST['comment'])) {
            $model = new Gallery();
            $comment = htmlspecialchars($_POST['comment']);
            $imageId = $_POST['image_id'];

            if ($model->addComment($_SESSION['user_id'], $imageId, $comment)) {
                
                // --- NOTIFICATION EMAIL ---
                $owner = $model->getImageOwner($imageId);
                if ($owner && $owner['notification_active'] && $owner['email']) {
                    $subject = "Nouveau commentaire sur Camagru";
                    $message = "Une nouvelle personne a commenté votre photo :\n\n\"$comment\"\n\nConnectez-vous pour voir !";
                    $headers = "From: no-reply@camagru.fr";
                    mail($owner['email'], $subject, $message, $headers);
                }
                
                echo json_encode([
                    'success' => true,
                    'comment' => $comment,
                    'username' => $_SESSION['username'],
                    'timestamp' => time()
                ]); exit;
            }
        }
        echo json_encode(['success' => false]); exit;
    }

    public function delete() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']); exit;
        }

        if (isset($_POST['image_id'])) {
            $model = new Gallery();
            if ($model->deleteImage($_POST['image_id'], $_SESSION['user_id'])) {
                echo json_encode(['success' => true]); exit;
            }
        }
        echo json_encode(['success' => false]); exit;
    }
}
?>