<?php namespace Olive\Http;

class File {
    public $name, $type, $tmp_name, $error, $size;

    /**
     * File constructor.
     *
     * @param array|NULL $file (Use $_FILES for this)
     */
    public function __construct(array &$file = null) {
        if($file != null) foreach($file as $k => $v)
            $this->{$k} = $v;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public static function createPath($path) {
        return mkdir($path, 0755, true);
    }

    public function gotFile() {
        return $this->tmp_name != null;
    }

    public function hasError() {
        return $this->error != 0;
    }

    public function copy($destDir, $destFile = null, $replace = false, $createPath = true) {
        return $this->transport($destDir, $destFile, $replace, $createPath);
    }


    /**
     *
     * @return string (returns the file name with extension)
     */
    public function getBaseName() {
        return mb_basename($this->name);
    }

    public function move($destDir, $destFile = null, $replace = false, $createPath = true) {
        return $this->transport($destDir, $destFile, $replace, $createPath, false);
    }

    public function moveAsUploadedFile($targetdir, $replace = false, $target_filename = null, $createPath = true) {
        $p = "$targetdir/" . ($target_filename == null ? $this->getBaseName() : $target_filename);

        if(file_exists($p))
            if($replace)
                unlink($p);
            else return false;

        if($createPath && !is_dir($targetdir))
            mkdir($targetdir, 755, true);

        $r = move_uploaded_file($this->tmp_name, $p);
        if($r) touch($p);

        return $r;
    }

    public function getExtension($tolower = true) {
        $xtmp = explode(".", $this->getBaseName());
        if($tolower)
            return strtolower(array_pop($xtmp));

        return array_pop($xtmp);
    }

    /**
     * @param array $desire (user lowercase)
     *
     * @return bool
     */
    public function validateExtension(array $desire) {
        return in_array($this->getExtension(), $desire);
    }

    /**
     * @param array $desire
     *
     * @return bool
     */
    public function validateType(array $desire) {
        return in_array($this->type, $desire);
    }

    /**
     * @param int $min
     * @param int $max
     *
     * @return bool
     */
    public function validateSize($min, $max) {
        return ($this->size >= $min) && ($this->size <= $max);
    }

    private function transport($destDir, $destFile = null, $replace = false, $createPath = true, $keepSource = true) {
        $p = "$destDir/" . ($destFile == null ? $this->getBaseName() : $destFile);
        if(file_exists($p))
            if($replace)
                unlink($p);
            else
                return false;

        if($createPath && !is_dir($destDir)) mkdir($destDir, 755, true);
        if(!$keepSource)
            $r = rename($this->tmp_name, $p);
        else
            $r = copy($this->tmp_name, $p);

        //change modified date
        if($r) touch($p);

        return $r;

    }

}
