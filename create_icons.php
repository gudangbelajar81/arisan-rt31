<?php
function resizeImage($source, $destination, $width, $height) {
    list($orig_width, $orig_height, $type) = getimagesize($source);
    $image_p = imagecreatetruecolor($width, $height);
    
    // Preserve transparency
    imagealphablending($image_p, false);
    imagesavealpha($image_p, true);
    $transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);
    imagefilledrectangle($image_p, 0, 0, $width, $height, $transparent);
    
    if ($type == IMAGETYPE_PNG) {
        $image = imagecreatefrompng($source);
    } elseif ($type == IMAGETYPE_JPEG) {
        $image = imagecreatefromjpeg($source);
    } else {
        return false;
    }
    
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
    imagepng($image_p, $destination);
    return true;
}

resizeImage('logo_m.png', 'icon-192.png', 192, 192);
resizeImage('logo_m.png', 'icon-512.png', 512, 512);
echo "Icons created successfully.\n";
?>
