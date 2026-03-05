<?php
require_once ROOT . '/config/database.php';
require_once ROOT . '/models/ImageProcessor.php';

class EditorController {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT filename FROM images WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $images = $stmt->fetchAll();
        require VIEWS . '/editor.php';
    }

    public function save() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Session expired']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['success' => false, 'error' => 'CSRF error']);
            exit;
        }

        if (!isset($input['image']) || !isset($input['filter'])) {
            echo json_encode(['success' => false, 'error' => 'Missing data']);
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
                echo json_encode(['success' => false, 'error' => 'Database error']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Image processing error']);
        }
    }
}
?>