<?php
class ImageProcessor {
    
    public function mergeAndSave($base64_data, $filter_path, $user_id, $meta = null) {
        
        $data = explode(',', $base64_data);
        $clean_base64 = base64_decode(end($data));
        if (!$clean_base64) return false;

        $img_info = getimagesizefromstring($clean_base64);
        if ($img_info === false) return false;
        
        $source = imagecreatefromstring($clean_base64);
        $filter = @imagecreatefrompng($filter_path);
        
        if (!$source || !$filter) return false;

        imagealphablending($source, true);
        imagesavealpha($source, true);
        imagealphablending($filter, true);
        imagesavealpha($filter, true);

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $origFilterW = imagesx($filter);
        $origFilterH = imagesy($filter);

        if ($meta && isset($meta['x'], $meta['y'], $meta['w'], $meta['h'])) {
            $dstX = (int) $meta['x'];
            $dstY = (int) $meta['y'];
            $newFilterW = (int) $meta['w'];
            $newFilterH = (int) $meta['h'];
            imagecopyresampled($source, $filter, $dstX, $dstY, 0, 0, $newFilterW, $newFilterH, $origFilterW, $origFilterH);
        } else {
            $dest_x = (int) (($srcW - $origFilterW) / 2);
            $dest_y = (int) (($srcH - $origFilterH) / 2);
            imagecopy($source, $filter, $dest_x, $dest_y, 0, 0, $origFilterW, $origFilterH);
        }

        $filename = 'img_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $filepath = $uploadDir . $filename;
        $saved = imagepng($source, $filepath);
        
        imagedestroy($source);
        imagedestroy($filter);

        return $saved ? $filename : false;
    }

    public function uploadProfilePicture($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        
        if ($file['size'] > 2 * 1024 * 1024) return false;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) return false;

        $src = null;
        switch ($mime) {
            case 'image/jpeg': $src = imagecreatefromjpeg($file['tmp_name']); break;
            case 'image/png':  $src = imagecreatefrompng($file['tmp_name']); break;
            case 'image/gif':  $src = imagecreatefromgif($file['tmp_name']); break;
        }

        if (!$src) return false;

        $width = imagesx($src);
        $height = imagesy($src);
        $thumbSize = 150;
        $thumb = imagecreatetruecolor($thumbSize, $thumbSize);

        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
        imagefilledrectangle($thumb, 0, 0, $thumbSize, $thumbSize, $transparent);

        $srcX = 0; $srcY = 0;
        $smallestSide = min($width, $height);
        
        if ($width > $height) {
            $srcX = ($width - $height) / 2;
        } else {
            $srcY = ($height - $width) / 2;
        }

        imagecopyresampled($thumb, $src, 0, 0, $srcX, $srcY, $thumbSize, $thumbSize, $smallestSide, $smallestSide);

        $filename = 'avatar_' . uniqid() . '.png';
        $uploadDir = __DIR__ . '/../public/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $saved = imagepng($thumb, $uploadDir . $filename);
        
        imagedestroy($src);
        imagedestroy($thumb);

        return $saved ? $filename : false;
    }
}
?>