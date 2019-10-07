<?php

use Olive\Http\Linker;
use Olive\Http\Response;
use Olive\MVC\ContentResult;
use Olive\MVC\EmptyResult;
use Olive\MVC\JsonResult;
use Olive\MVC\ViewResult;
use Olive\Support\Html\Html;
use Olive\Util\Text;
use Olive\Util\WithObject;

/**
 * @param string $source
 *
 * @param bool $external
 * @return string
 */
function url($source = '', $external = false) {
    if ($external)
        return Linker::externalLink($source);
    return Linker::internalLink($source);
}

/**
 * @param $source
 * @param bool $external
 * @param string $full_port
 * @return string
 */
function linkEx($source, $external = false, $full_port = '') {
    return Linker::linkExtended($source, $external, $full_port);
}

/**
 * @param mixed $content
 * @param bool $alive
 * @param bool $preventCache
 */
function json_dump($content, $alive = false, $preventCache = false) {
    Response::jsonDump($content, $alive, $preventCache);
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
function domElement($name, $attrs_or_content = null, $content = null, $tagtype = Html::TAG_AUTO_DETECT) {
    return Html::domElement($name, $attrs_or_content, $content, $tagtype);
}


/**
 * @param object $object
 * @return WithObject
 */
function with($object) {
    return new WithObject($object);
}


/**
 * @param $baseDir
 * @param $path
 * @return bool
 */
function isSubdirOf($baseDir, $path) {
    $baseReal = realpath($baseDir);
    $pathReal = realpath($path);
    return Text::startsWith($baseReal, $pathReal);
}

/**
 * @param $target_url
 */
function redirect($target_url) {
    Response::redirect($target_url);
}

function contentResult() {
    return new ContentResult;
}

function emptyResult() {
    return new EmptyResult;
}

/**
 * @param array|object $value
 * @param int $options
 * @param int $depth
 * @return JsonResult
 */
function jsonResult($value, int $options = 0, int $depth = JSON_PARTIAL_OUTPUT_ON_ERROR) {
    return new JsonResult($value, $options, $depth);
}

function viewResult(string $view = null) {
    return new ViewResult($view);
}
function object(){
    return new stdClass();
}
