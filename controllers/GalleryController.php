<?php
require_once ROOT . '/models/Gallery.php';

class GalleryController {

    private function checkCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Erreur CSRF']);
            exit;
        }
    }

    public function index() {
        $galleryModel = new Gallery();
        $limit = 6;
        
        // On récupère JUSTE le last_id
        $lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : null;

        $images = $galleryModel->getImages($limit, $lastId);
        
        foreach ($images as &$img) {
            $img['likes'] = $galleryModel->getLikeCount($img['id']);
            $img['comments'] = $galleryModel->getComments($img['id']);
        }
        unset($img);

        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            ob_start();
            foreach ($images as $img) {
                include VIEWS . '/partials/image_card.php';
            }
            $htmlContent = ob_get_clean();

            if (ob_get_length()) ob_clean(); 

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'html' => $htmlContent,
                'has_more' => count($images) === $limit 
            ]);
            exit;
        }

        require VIEWS . '/gallery.php';
    }

    // ... (Garde tes fonctions like, comment, delete inchangées ci-dessous) ...
    public function like() {
        $this->checkCsrf();
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }
        if (isset($_POST['image_id'])) {
            $model = new Gallery();
            $status = $model->toggleLike($_SESSION['user_id'], $_POST['image_id']);
            $newCount = $model->getLikeCount($_POST['image_id']);
            echo json_encode(['success'=>true, 'status'=>$status, 'new_count'=>$newCount]); exit;
        }
        echo json_encode(['success'=>false]); exit;
    }

    public function comment() {
        $this->checkCsrf();
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }
        if (isset($_POST['image_id']) && !empty($_POST['comment'])) {
            $model = new Gallery();
            if ($model->addComment($_SESSION['user_id'], $_POST['image_id'], htmlspecialchars($_POST['comment']))) {
                 $owner = $model->getImageOwner($_POST['image_id']);
                 if ($owner && $owner['notification_active'] && $owner['email']) {
                     mail($owner['email'], "Commentaire Camagru", "Nouveau com", "From: no-reply@camagru.fr");
                 }
                 echo json_encode(['success'=>true, 'comment'=>htmlspecialchars($_POST['comment']), 'username'=>$_SESSION['username']]); exit;
            }
        }
        echo json_encode(['success'=>false]); exit;
    }

    public function delete() {
        $this->checkCsrf();
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }
        if (isset($_POST['image_id'])) {
            $model = new Gallery();
            if ($model->deleteImage($_POST['image_id'], $_SESSION['user_id'])) {
                echo json_encode(['success'=>true]); exit;
            }
        }
        echo json_encode(['success'=>false]); exit;
    }
}
?>