<?php
class ImageProcessor {
    
    // Fusionne une image (Base64) avec un filtre (chemin PNG)
    public function mergeAndSave($base64_data, $filter_path, $user_id) {
        // 1. Nettoyage du Base64
        $data = explode(',', $base64_data);
        $clean_base64 = base64_decode(end($data));
        
        if (!$clean_base64) return false;

        // 2. Création des ressources d'image GD
        $source = imagecreatefromstring($clean_base64); // Photo webcam
        $filter = imagecreatefrompng($filter_path);     // Sticker (ex: lunettes)
        
        if (!$source || !$filter) return false;

        // 3. Gestion de la transparence (Alpha Channel)
        imagealphablending($filter, true);
        imagesavealpha($filter, true);

        // 4. Redimensionnement du filtre (Optionnel : ici on le met à 100x100px et on le place en haut à gauche)
        // Pour un vrai projet, tu peux passer les coordonnées X/Y depuis le JS
        $filter_w = imagesx($filter);
        $filter_h = imagesy($filter);
        
        // On place le filtre au centre (exemple simpliste)
        $dest_x = (imagesx($source) - $filter_w) / 2;
        $dest_y = (imagesy($source) - $filter_h) / 2;

        imagecopy($source, $filter, $dest_x, $dest_y, 0, 0, $filter_w, $filter_h);

        // 5. Sauvegarde sur le disque
        $filename = 'img_' . $user_id . '_' . time() . '.png';
        $filepath = __DIR__ . '/../public/uploads/' . $filename;
        
        // S'assurer que le dossier existe
        if (!is_dir(__DIR__ . '/../public/uploads/')) {
            mkdir(__DIR__ . '/../public/uploads/', 0777, true);
        }

        imagepng($source, $filepath);
        
        // Libération de la mémoire
        imagedestroy($source);
        imagedestroy($filter);

        return $filename; // On retourne le nom pour l'insérer en BDD
    }
}
?>
