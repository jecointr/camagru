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
            echo json_encode(['success' => false, 'error' => 'Session expirée']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['image']) || !isset($input['filter'])) {
            echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            exit;
        }

        // 1. Traitement Webcam
        $data = explode(',', $input['image']);
        $base64 = count($data) > 1 ? $data[1] : $data[0];
        $sourceStr = base64_decode($base64);
        $sourceImage = imagecreatefromstring($sourceStr);

        if (!$sourceImage) {
            echo json_encode(['success' => false, 'error' => 'Flux webcam invalide']);
            exit;
        }

        // 2. Traitement Filtre
        $filterName = basename($input['filter']);
        $filterPath = ROOT . '/public/img/filters/' . $filterName;

        if (file_exists($filterPath)) {
            // 👇 LE FIX EST ICI : On utilise @ pour couper le Warning PHP
            $filterImage = @imagecreatefrompng($filterPath);
            
            // 👇 ET ICI : On vérifie si le chargement a réussi
            if (!$filterImage) {
                echo json_encode(['success' => false, 'error' => "Le fichier $filterName est corrompu (pas un vrai PNG)."]);
                exit;
            }
            
            imagealphablending($filterImage, true);
            imagesavealpha($filterImage, true);
            
            $srcW = imagesx($sourceImage);
            $srcH = imagesy($sourceImage);
            $fltW = imagesx($filterImage);
            $fltH = imagesy($filterImage);
            
            $dstX = ($srcW - $fltW) / 2;
            $dstY = ($srcH - $fltH) / 2;

            imagecopy($sourceImage, $filterImage, $dstX, $dstY, 0, 0, $fltW, $fltH);
            imagedestroy($filterImage);
        } else {
            echo json_encode(['success' => false, 'error' => 'Filtre introuvable']);
            exit;
        }

        // 3. Sauvegarde
        $filename = uniqid('camagru_') . '.png';
        $uploadDir = ROOT . '/public/uploads/';
        
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        if (imagepng($sourceImage, $uploadDir . $filename)) {
            imagedestroy($sourceImage);
            
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO images (user_id, image_path) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $filename]);

            echo json_encode(['success' => true, 'filename' => $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur écriture disque']);
        }
    }
}