<?php namespace Olive\Html;
abstract class Form extends Html {

    #region Consts: ENCTYPES
    const ENCTYPE_MULTIPART = 'multipart/form-data';
    const ENCTYPE_PLAIN     = 'text/plain';
    #endregion

    #region Consts: Methods
    const METHOD_POST = 'post';
    const METHOD_GET  = 'get';
    #endregion

    #region Builders
    /**
     * @param string|array $action see: {@see \Olive\util\html::parseURL() html::parseURL}
     * @param array $attribs
     * @return string
     */
    public static function open($action, array $attribs = []) {

        $attribs = array_merge([
            'method'         => self::METHOD_POST,
            'enctype'        => self::ENCTYPE_PLAIN,
            'accept-charset' => 'UTF-8',
        ], $attribs);

        if($action)
            $attribs['action'] = self::parseURL($action);

        $tagStr = self::tag('form', $attribs);
        return substr($tagStr, 0, -1 * strlen('</form>')) . '';
    }

    public static function close() {
        return '</form>';
    }
    #endregion
}