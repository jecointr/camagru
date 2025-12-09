<?php
require_once __DIR__ . '/../models/ImageProcessor.php';
require_once __DIR__ . '/../models/Image.php'; // Modèle BDD simple (insert)

class EditorController {
    
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        // Ici, tu devrais aussi charger les anciennes images de l'utilisateur
        // $images = (new Image())->getByUser($_SESSION['user_id']);
        require __DIR__ . '/../views/editor.php';
    }

    public function save() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            exit;
        }

        // Récupération du JSON brut (car fetch envoie du JSON, pas du $_POST standard)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['image']) && isset($input['filter'])) {
            
            // Sécurité : Vérifier que le filtre est légitime (pas de ../../../etc/passwd)
            $allowed_filters = ['glasses.png', 'hat.png', 'tree.png'];
            if (!in_array($input['filter'], $allowed_filters)) {
                echo json_encode(['success' => false, 'error' => 'Filtre invalide']);
                exit;
            }

            $filterPath = __DIR__ . '/../public/img/filters/' . $input['filter'];
            
            $processor = new ImageProcessor();
            $filename = $processor->mergeAndSave($input['image'], $filterPath, $_SESSION['user_id']);

            if ($filename) {
                // Sauvegarde en BDD
                // Supposons une classe Image avec une méthode save($userId, $path)
                $db = Database::getInstance();
                $stmt = $db->prepare("INSERT INTO images (user_id, image_path) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $filename]);
                
                echo json_encode(['success' => true, 'filename' => $filename]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur traitement image']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Données manquantes']);
        }
    }
}
?>
