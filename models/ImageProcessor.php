<?php
class ImageProcessor {
    
    // On ajoute un argument optionnel $meta pour les coordonnées
    public function mergeAndSave($base64_data, $filter_path, $user_id, $meta = null) {
        
        // 1. Décodage et Sécurité
        $data = explode(',', $base64_data);
        $clean_base64 = base64_decode(end($data));
        if (!$clean_base64) return false;

        $img_info = getimagesizefromstring($clean_base64);
        if ($img_info === false) return false;
        
        // 2. Création
        $source = imagecreatefromstring($clean_base64);
        $filter = @imagecreatefrompng($filter_path);
        
        if (!$source || !$filter) return false;

        // 3. Transparence
        imagealphablending($source, true);
        imagesavealpha($source, true);
        imagealphablending($filter, true);
        imagesavealpha($filter, true);

        // --- 4. POSITIONNEMENT AVANCÉ (NOUVEAU) ---
        
        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $origFilterW = imagesx($filter);
        $origFilterH = imagesy($filter);

        if ($meta && isset($meta['x'], $meta['y'], $meta['w'], $meta['h'])) {
            // Position définie par le Drag & Drop JS
            $dstX = (int) $meta['x'];
            $dstY = (int) $meta['y'];
            $newFilterW = (int) $meta['w'];
            $newFilterH = (int) $meta['h'];

            // On redimensionne le filtre et on le colle
            // imagecopyresampled(dst, src, dstX, dstY, srcX, srcY, dstW, dstH, srcW, srcH)
            imagecopyresampled($source, $filter, $dstX, $dstY, 0, 0, $newFilterW, $newFilterH, $origFilterW, $origFilterH);
        } else {
            // Fallback : Centrage par défaut
            $dest_x = (int) (($srcW - $origFilterW) / 2);
            $dest_y = (int) (($srcH - $origFilterH) / 2);
            imagecopy($source, $filter, $dest_x, $dest_y, 0, 0, $origFilterW, $origFilterH);
        }

        // 5. Sauvegarde
        $filename = 'img_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $filepath = $uploadDir . $filename;
        $saved = imagepng($source, $filepath);
        
        imagedestroy($source);
        imagedestroy($filter);

        return $saved ? $filename : false;
    }
}
?>