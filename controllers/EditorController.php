<?php
require_once ROOT . '/config/database.php';
require_once ROOT . '/models/ImageProcessor.php'; // On inclut le nouveau modèle blindé

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

        // Récupération du JSON envoyé par le JS
        $input = json_decode(file_get_contents('php://input'), true);

        // --- 1. SÉCURITÉ CSRF (Depuis le JSON) ---
        if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['success' => false, 'error' => 'Erreur CSRF']);
            exit;
        }

        // Validation des entrées
        if (!isset($input['image']) || !isset($input['filter'])) {
            echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            exit;
        }

        // --- 2. TRAITEMENT VIA LE MODÈLE (Propre & Sécurisé) ---
        // On ne fait plus de GD ici. On appelle ImageProcessor.
        
        $processor = new ImageProcessor();
        $filterPath = ROOT . '/public/img/filters/' . basename($input['filter']);

        // Appel de la méthode mergeAndSave qu'on vient de sécuriser
        // Elle retourne le nom du fichier si OK, ou false si erreur/hack
        $filename = $processor->mergeAndSave($input['image'], $filterPath, $_SESSION['user_id']);

        if ($filename) {
            // --- 3. SAUVEGARDE EN BDD ---
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("INSERT INTO images (user_id, image_path) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $filename]);

                echo json_encode(['success' => true, 'filename' => $filename]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => 'Erreur BDD']);
            }
        } else {
            // Si le modèle a renvoyé false (ex: ce n'était pas une image valide, ou erreur d'écriture)
            echo json_encode(['success' => false, 'error' => 'Erreur lors du traitement de l\'image (Fichier invalide ?)']);
        }
    }
}