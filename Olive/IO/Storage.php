<?php namespace Olive\IO;

use Olive\Exceptions\IOException;

/**
 * Class Storage
 * @package Olive\IO
 */
abstract class Storage
{

    protected const VISIBILITY_PUBLIC  = 'public';
    protected const VISIBILITY_PRIVATE = 'private';

    protected $root, $visibility;

    /**
     * @param string $filename
     * @return string
     */
    public static function url($filename) {
        return url(static::getInstance()->root . '/' . $filename);
    }

    /**
     * @return static
     */
    private static function getInstance() {
        /** @noinspection InfinityLoopInspection */
        return static::getInstance(); #todo remove this function and replace self::getInstance by static::getInstance
    }

    protected function ensureRoot() {
        $root = $this->root;
        if (!is_dir($root)) {
            $umask = umask(0);
            if (!@mkdir($root, $this->permissionMap(), true)) {
                $mkdirError = error_get_last();
            }
            umask($umask);
            clearstatcache(false, $root);
            if (!is_dir($root)) {
                $errorMessage = $mkdirError['message'] ?? '';
                throw new IOException(sprintf('Impossible to create the root directory "%s". %s', $root, $errorMessage));
            }
        }
    }

    private function permissionMap($type = 'dir') {
        $type = $type === 'dir' ? $type : 'file';
        return ['file' => [
            'public'  => 0644,
            'private' => 0600,
        ], 'dir'       => [
            'public'  => 0755,
            'private' => 0700,
        ]][$type][$this->visibility];
    }


}
