<?php
class ImageProcessor {
    
    // Fusionne une image (Base64) avec un filtre (chemin PNG)
    public function mergeAndSave($base64_data, $filter_path, $user_id) {
        
        // --- 1. SÉCURITÉ & NETTOYAGE ---

        // On sépare l'en-tête "data:image/png;base64," du contenu
        $data = explode(',', $base64_data);
        $clean_base64 = base64_decode(end($data));
        
        if (!$clean_base64) return false;

        // VÉRIFICATION DE SÉCURITÉ : Est-ce vraiment une image ?
        // getimagesizefromstring renvoie false si ce n'est pas une image valide
        $img_info = getimagesizefromstring($clean_base64);
        if ($img_info === false) {
            // Ce n'est pas une image (ou c'est un fichier corrompu/malveillant)
            return false;
        }

        // Optionnel : Vérifier le type MIME pour être strict
        $allowed_types = ['image/png', 'image/jpeg'];
        if (!in_array($img_info['mime'], $allowed_types)) {
            return false; 
        }

        // --- 2. CRÉATION DES RESSOURCES ---

        $source = imagecreatefromstring($clean_base64); // Photo webcam
        $filter = @imagecreatefrompng($filter_path);    // Sticker
        
        if (!$source || !$filter) return false;

        // --- 3. CORRECTION TRANSPARENCE (FIX) ---
        
        // A. Sur la SOURCE (Webcam) : On active le blending pour qu'elle accepte
        // la transparence du calque qu'on va poser dessus.
        imagealphablending($source, true);
        imagesavealpha($source, true);

        // B. Sur le FILTRE (Sticker) : On préserve son canal Alpha
        imagealphablending($filter, true);
        imagesavealpha($filter, true);

        // --- 4. POSITIONNEMENT & FUSION ---

        $filter_w = imagesx($filter);
        $filter_h = imagesy($filter);
        $source_w = imagesx($source);
        $source_h = imagesy($source);
        
        // On place le filtre au centre
        $dest_x = (int) (($source_w - $filter_w) / 2);
        $dest_y = (int) (($source_h - $filter_h) / 2);

        // Fusion
        imagecopy($source, $filter, $dest_x, $dest_y, 0, 0, $filter_w, $filter_h);

        // --- 5. SAUVEGARDE ---

        $filename = 'img_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
        $uploadDir = __DIR__ . '/../public/uploads/';
        $filepath = $uploadDir . $filename;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $saved = imagepng($source, $filepath);
        
        // Libération de la mémoire
        imagedestroy($source);
        imagedestroy($filter);

        return $saved ? $filename : false;
    }
}
?>