<?php
require_once ROOT . '/config/database.php';
require_once ROOT . '/models/ImageProcessor.php';

class EditorController {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        require VIEWS . '/editor.php';
    }

    public function save() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Session expirée']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['success' => false, 'error' => 'Erreur CSRF']);
            exit;
        }

        if (!isset($input['image']) || !isset($input['filter'])) {
            echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            exit;
        }

        $processor = new ImageProcessor();
        $filterPath = ROOT . '/public/img/filters/' . basename($input['filter']);

        $meta = isset($input['meta']) ? $input['meta'] : null;

        $filename = $processor->mergeAndSave($input['image'], $filterPath, $_SESSION['user_id'], $meta);

        if ($filename) {
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("INSERT INTO images (user_id, filename) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $filename]);

                echo json_encode(['success' => true, 'filename' => $filename]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => 'Erreur BDD']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur traitement image']);
        }
    }
}
?>