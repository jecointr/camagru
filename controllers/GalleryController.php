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
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        if (isset($_POST['image_id'])) {
            $model = new Gallery();
            $status = $model->toggleLike($_SESSION['user_id'], $_POST['image_id']); // Assumons que toggleLike retourne 'liked' ou 'unliked'

            // Récupérer le nouveau compte pour l'envoyer au client
            $newCount = $model->getLikeCount($_POST['image_id']);

            echo json_encode(['success' => true, 'status' => $status, 'new_count' => $newCount]);
            exit;
        }
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID image manquant']);
        exit;
    }

    public function comment() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        if (isset($_POST['image_id']) && !empty($_POST['comment'])) {
            $model = new Gallery();
            $comment = htmlspecialchars($_POST['comment']);
            $imageId = $_POST['image_id'];

            if ($model->addComment($_SESSION['user_id'], $imageId, $comment)) {
                
                // (La logique d'envoi de mail de notification reste ici, elle est asynchrone)
                $owner = $model->getImageOwner($imageId);
                // ... (Logique d'envoi de mail) ...
                
                // On renvoie les données du nouveau commentaire pour l'affichage
                echo json_encode([
                    'success' => true,
                    'comment' => $comment,
                    'username' => $_SESSION['username'], // On récupère le pseudo de la session
                    'timestamp' => time() // Horodatage pour affichage (optionnel)
                ]);
                exit;
            }
        }
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Données manquantes ou erreur BDD']);
        exit;
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