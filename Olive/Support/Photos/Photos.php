<?php namespace Olive\Support\Photos;

use Olive\Exceptions\PhotoException;

abstract class Photos {

    public static function resize($file, $w, $new) {
        $info = getimagesize($file);
        $mime = $info['mime'];
        switch($mime) {
            case 'image/jpg':
                $image_create_func = 'imagecreatefromjpeg';
                $image_save_func   = 'imagejpeg';
                break;
            case 'image/jpeg':
                $image_create_func = 'imagecreatefromjpeg';
                $image_save_func   = 'imagejpeg';
                break;
            case 'image/png':
                $image_create_func = 'imagecreatefrompng';
                $image_save_func   = 'imagepng';
                break;

            case 'image/gif':
                $image_create_func = 'imagecreatefromgif';
                $image_save_func   = 'imagegif';
                break;

            default:
                throw new PhotoException('Unknown image type.');
        }
        $img = $image_create_func($file);
        list($width, $height) = getimagesize($file);

        $newHeight = ($height / $width) * $w;
        $tmp       = imagecreatetruecolor($w, $newHeight);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $w, $newHeight, $width, $height);

        if(file_exists($new))
            unlink($new);

        $image_save_func($tmp, "$new", 100);
        touch($new);
    }

    public static function getSize($file) {
        return getimagesize($file);
    }

    public static function cropSquare($file, $new = null) {
        $new = $new ? $new : $file;

        $r      = getimagesize($file);
        $width  = $r[0];
        $height = $r[1];
        $type   = strtolower($r['mime']);
        $min    = min($width, $height);
        $x      = ($width > $height) ? ($width - $height) / 2 : 0;
        $y      = ($width < $height) ? ($height - $width) / 2 : 0;
        switch($type) {
            case 'image/gif':
                $src = imagecreatefromgif($file);
                break;
            case 'image/jpg':
            case 'image/jpeg':
                $src = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $src = imagecreatefrompng($file);
                break;
            default:
                throw new PhotoException('Unknown image type.');
        }
        //var_dump($src);
        $dest = imagecreatetruecolor($min, $min);
        imagecopy($dest, $src, 0, 0, $x, $y, $width, $height);
        switch($type) {
            case 'image/gif':
                imagegif($dest, $new);
                break;
            case 'image/jpg':
            case 'image/jpeg':
                imagejpeg($dest, $new, 100);
                break;
            case 'image/png':
                imagepng($dest, $new, 100);
                break;
        }
        touch($new);

        imagedestroy($src);
        imagedestroy($dest);
    }

    public static function convertToJPG($file, $new) {
        $ret = imagejpeg(imagecreatefromstring(file_get_contents($file)), $new, 100);
        touch($file);

        return $ret;
    }

}
