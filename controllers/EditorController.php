<?php
require_once ROOT . '/config/database.php';

class EditorController {

    public function index() {
        // Sécurité : Seulement si connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        require VIEWS . '/editor.php';
    }

    public function save() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            exit;
        }

        // Lire le JSON reçu
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['image']) || !isset($input['filter'])) {
            echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            exit;
        }

        // 1. Décoder l'image Base64 (Webcam)
        $data = explode(',', $input['image']);
        $base64 = end($data);
        $sourceImage = imagecreatefromstring(base64_decode($base64));
        if (!$sourceImage) {
            echo json_encode(['success' => false, 'error' => 'Image invalide']);
            exit;
        }

        // 2. Charger le filtre (Sticker)
        // ATTENTION : Tu dois créer ce dossier et y mettre des images !
        $filterName = basename($input['filter']); // Sécurité path traversal
        $filterPath = ROOT . '/public/img/filters/' . $filterName;

        if (file_exists($filterPath)) {
            $filterImage = imagecreatefrompng($filterPath);
            
            // Préserver la transparence
            imagealphablending($filterImage, true);
            imagesavealpha($filterImage, true);
            
            // Superposition (Centré pour l'exemple)
            // En bonus, tu pourrais gérer le X/Y envoyé par le JS
            $dst_x = (imagesx($sourceImage) - imagesx($filterImage)) / 2;
            $dst_y = (imagesy($sourceImage) - imagesy($filterImage)) / 2;
            
            imagecopy($sourceImage, $filterImage, $dst_x, $dst_y, 0, 0, imagesx($filterImage), imagesy($filterImage));
            imagedestroy($filterImage);
        }

        // 3. Sauvegarder le résultat
        $filename = uniqid('img_') . '.png';
        $uploadDir = ROOT . '/public/uploads/';
        
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $savePath = $uploadDir . $filename;
        imagepng($sourceImage, $savePath);
        imagedestroy($sourceImage);

        // 4. Inserer en Base de Données
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO images (user_id, image_path) VALUES (?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $filename])) {
            echo json_encode(['success' => true, 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur SQL']);
        }
    }
}