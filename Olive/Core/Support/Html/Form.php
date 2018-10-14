<?php namespace Olive\Support\Html;

use Olive\Exceptions\CSRFTokenInvalid;
use Olive\Security\CSRFToken;
use Olive\Util\DateTime;

abstract class Form extends Html {

    #region Consts: EncTypes
    const ENCTYPE_MULTIPART = 'multipart/form-data';
    const ENCTYPE_PLAIN     = 'text/plain';
    #endregion

    #region Consts: Methods
    const METHOD_POST = 'post';
    const METHOD_GET  = 'get';
    #endregion

    #region Consts: InputTypes
    const T_TEXT           = 'text';
    const T_NUMBER         = 'number';
    const T_EMAIL          = 'email';
    const T_TEL            = 'tel';
    const T_PASSWORD       = 'password';
    const T_RANGE          = 'range';
    const T_HIDDEN         = 'hidden';
    const T_DATE           = 'date';
    const T_DATETIME       = 'datetime';
    const T_MONTH          = 'month';
    const T_COLOR          = 'color';
    const T_DATETIME_LOCAL = 'datetime-local';
    const T_TIME           = 'time';
    const T_WEEK           = 'week';
    const T_URL            = 'url';
    const T_FILE           = 'file';
    const T_CHECKBOX       = 'checkbox';
    const T_RADIO          = 'radio';
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

        $tagStr = static::tag('form', $attribs);
        return substr($tagStr, 0, -1 * strlen('</form>')) . '';
    }

    public static function close() {
        return '</form>';
    }

    public static function input($name, $value = NULL, $type = self::T_TEXT, $attribs = []) {

        $attribs['type']  = $type;
        $attribs['name']  = $name;
        $attribs['value'] = $value;

        return static::tag('input', $attribs);

    }

    public static function hidden($name, $value = NULL, $attribs = []) {
        return static::input($name, $value, static::T_HIDDEN, $attribs);
    }

    public static function token(CSRFToken $csrf) {
        if($csrf == NULL)
            throw new CSRFTokenInvalid('Missing CSRFToken object');

        $out = '';
        if($key = $csrf->getKey())
            $out .= static::hidden('_csrf_key', $key);
        return $out . static::hidden('_csrf_token', $csrf->getToken());
    }

    public static function text($name, $value = NULL, $attribs = []) {
        return static::input($name, $value, static::T_TEXT, $attribs);
    }

    public static function number($name, $value = NULL, $attribs = []) {
        return static::input($name, $value, static::T_NUMBER, $attribs);
    }

    public static function password($name, $attribs = []) {
        return static::input($name, NULL, static::T_PASSWORD, $attribs);
    }

    public static function range($name, $value = NULL, $attribs = []) {
        return static::input($name, $value, static::T_RANGE, $attribs);
    }

    public static function email($name, $value = NULL, $attribs = []) {
        return static::input($name, $value, static::T_EMAIL, $attribs);
    }

    public static function tel($name, $value = NULL, $attribs = []) {
        return static::input($name, $value, static::T_TEL, $attribs);
    }

    public static function date($name, $value = NULL, $attribs = []) {
        if(is_object($value)) {
            if($value instanceof DateTime)
                $value = $value->legacyFormat('Y-m-d');
            elseif($value instanceof \DateTime)
                $value = $value->format('Y-m-d');
        }
        return static::input($name, $value, static::T_DATE, $attribs);
    }

    public static function datetime($name, $value = NULL, $attribs = []) {
        if(is_object($value)) {
            if($value instanceof DateTime)
                $value = $value->legacyFormat(DateTime::RFC3339);
            elseif($value instanceof \DateTime)
                $value = $value->format(\DateTime::RFC3339);
        }
        return static::input($name, $value, static::T_DATETIME, $attribs);
    }

    public static function datetimeLocal($name, $value = NULL, $attribs = []) {
        if(is_object($value)) {
            if($value instanceof DateTime)
                $value = $value->legacyFormat('Y-m-d\TH:i');
            elseif($value instanceof \DateTime)
                $value = $value->format('Y-m-d\TH:i');
        }
        return static::input($name, $value, static::T_DATETIME_LOCAL, $attribs);
    }

    public static function time($name, $value = NULL, $attribs = []) {
        if(is_object($value)) {
            if($value instanceof DateTime)
                $value = $value->legacyFormat('H:i');
            elseif($value instanceof \DateTime)
                $value = $value->format('H:i');
        }
        return static::input($name, $value, static::T_TIME, $attribs);
    }

    public static function week($name, $value = NULL, $attribs = []) {
        if(is_object($value)) {
            if($value instanceof DateTime)
                $value = $value->legacyFormat('Y-\WW');
            elseif($value instanceof \DateTime)
                $value = $value->format('Y-\WW');
        }
        return static::input($name, $value, static::T_WEEK, $attribs);
    }

    public static function month($name, $value = NULL, $attribs = []) {
        if(is_object($value)) {
            if($value instanceof DateTime)
                $value = $value->legacyFormat('Y-m');
            elseif($value instanceof \DateTime)
                $value = $value->format('Y-m');
        }
        return static::input($name, $value, static::T_MONTH, $attribs);
    }


    public static function url($name, $value = NULL, $attribs = []) {
        return static::input($name, $value, static::T_URL, $attribs);
    }

    public static function color($name, $value = NULL, $attribs = []) {
        return static::input($name, $value, static::T_COLOR, $attribs);
    }

    public static function file($name, $value = NULL, $attribs = []) {
        return static::input($name, $value, static::T_FILE, $attribs);
    }

    public static function textarea($name, $value = NULL, $encode = FALSE, $attribs = []) {
        $attribs['name'] = $name;
        return static::tag('textarea', $attribs, $encode ? self::specialsEncode($value) : $value);
    }

    public static function select($name, $list = [], $selected = NULL,
                                  array $selectAttribs = [],
                                  array $optionsAttribs = [],
                                  array $optgroupsAttribs = []) {
        $html = '';
        foreach($list as $value => $item) {
            $html .= is_array($item)
                ? static::optgroup($item, $value, $selected, $optionsAttribs[$value] ?? [], $optgroupsAttribs[$value] ?? [])
                : static::option($value, $item, $selected === $value, $optionsAttribs[$value] ?? []);
        }
        $selectAttribs['name'] = $name;
        return self::tag('select', $selectAttribs, $html, static::TAG_NORMAL);
    }

    public static function checkbox($name, $value = 1, $checked = FALSE, $attribs = []) {
        if($checked) $attribs['checked'] = 'checked';
        return static::input($name, $value, static::T_CHECKBOX, $attribs);
    }

    public static function radio($name, $value = NULL, $checked = FALSE, $attribs = []) {

        if($checked) $attribs['checked'] = 'checked';

        return static::input($name, is_null($value) ? $name : $value, static::T_RADIO, $attribs);
    }
    #endregion

    #region Inner helpers
    protected static function optgroup($list, $label, $selected = NULL, $optionsAtrribs = [], $attribs = []) {

        $attribs['label'] = $label;
        $g                = '';

        foreach($list as $value => $content) {
            $g .= self::option($value, $content, $selected === $value, $optionsAtrribs[$value] ?? []);
        }

        return static::tag('optgroup', $attribs, $g);
    }

    private static function option($value = NULL, $content = NULL, $selected = FALSE, $attribs = []) {
        if($value != '')
            $attribs['value'] = $value;
        if($selected) $attribs['selected'] = 'selected';
        return static::tag('option', $attribs, $content, static::TAG_NORMAL);
    }
    #endregion
}