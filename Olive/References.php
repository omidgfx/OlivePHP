<?php

/**
 * Returns a url that is useful for using in href or src attribs for internal resources like
 * <code>/olivephp/your_resource.some</code>
 *
 * @param string $source
 *
 * @param bool $full
 * @return string
 */
function src($source = '', $full = false) {
    if($full)
        return Olive\Http\Linker::srcFull($source);
    return Olive\Http\Linker::src($source);
}

/**
 * @param $source
 * @param bool $full
 * @param string $full_port
 * @param null $full_protocol
 * @return string
 */
function srcExt($source, $full = false, $full_port = '', $full_protocol = null) {
    return Olive\Http\Linker::srcEx($source, $full, $full_port, $full_protocol);
}

/**
 * @param mixed $content
 * @param bool $alive
 * @param bool $preventCache
 */
function json_dump($content, $alive = false, $preventCache = false) {
    \Olive\Http\End::jsonDump($content, $alive, $preventCache);
}

/**
 * @param string $name Tag Name
 * @param array|string $attrs_or_content
 * @param string $content
 * @param int $tagtype Type of tag.
 * <b>{@see \Olive\Util\html::TAG_AUTO_DETECT}</b>,
 * <b>{@see \Olive\Util\html::TAG_NORMAL}</b>,
 * <b>{@see \Olive\Util\html::TAG_ENCLOSING}</b>,
 * <b>{@see \Olive\Util\html::TAG_EMPTY }</b>
 * @return string
 */
function tag($name, $attrs_or_content = null, $content = null, $tagtype = \Olive\Support\Html\Html::TAG_AUTO_DETECT) {
    return \Olive\Support\Html\Html::tag($name, $attrs_or_content, $content, $tagtype);
}

if(!function_exists("mb_basename")) {
    /**
     * Returns filename component of path
     * @link http://php.net/manual/en/function.basename.php
     * @param string $path <p>
     * A path.
     * </p>
     * <p>
     * On Windows, both slash (/) and backslash
     * (\) are used as directory separator character. In
     * other environments, it is the forward slash (/).
     * </p>
     * @return string the base name of the given path.
     * @since 4.0
     * @since 5.0
     */
    function mb_basename($path) {
        $separator = " qq ";
        $path      = preg_replace("/[^ ]/u", $separator . "\$0" . $separator, $path);
        $base      = basename($path);
        $base      = str_replace($separator, "", $base);
        return $base;
    }
}
/**
 * @param object $object
 * @return \Olive\Util\WithObject
 */

function with($object) {
    return new \Olive\Util\WithObject($object);
}


/**
 * @param $baseDir
 * @param $path
 * @return bool
 */
function isSubdirOf($baseDir, $path) {
    $baseReal = realpath($baseDir);
    $pathReal = realpath($path);
    return \Olive\Util\Text::startsWith($baseReal, $pathReal);
}