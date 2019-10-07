<?php namespace Olive\Support\Html;

use Olive\Exceptions\OliveException;
use Olive\Security\CSRFGuard;
use Olive\Util\DateTime;

abstract class Form extends Html
{

    #region Consts: EncTypes
    public const ENCTYPE_MULTIPART = 'multipart/form-data';
    public const ENCTYPE_PLAIN     = 'text/plain';
    #endregion

    #region Consts: Methods
    public const METHOD_POST = 'post';
    public const METHOD_GET  = 'get';
    #endregion

    #region Consts: InputTypes
    public const T_TEXT           = 'text';
    public const T_NUMBER         = 'number';
    public const T_EMAIL          = 'email';
    public const T_TEL            = 'tel';
    public const T_PASSWORD       = 'password';
    public const T_RANGE          = 'range';
    public const T_HIDDEN         = 'hidden';
    public const T_DATE           = 'date';
    public const T_DATETIME       = 'datetime';
    public const T_MONTH          = 'month';
    public const T_COLOR          = 'color';
    public const T_DATETIME_LOCAL = 'datetime-local';
    public const T_TIME           = 'time';
    public const T_WEEK           = 'week';
    public const T_URL            = 'url';
    public const T_FILE           = 'file';
    public const T_CHECKBOX       = 'checkbox';
    public const T_RADIO          = 'radio';
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

        if ($action)
            $attribs['action'] = self::parseURL($action);

        $tagStr = static::domElement('form', $attribs);
        return substr($tagStr, 0, -1 * strlen('</form>')) . '';
    }

    public static function close() {
        return '</form>';
    }

    /**
     * @param CSRFGuard $csrf
     * @return string
     * @throws OliveException
     */
    public static function token(CSRFGuard $csrf) {
        if ($csrf === null)
            throw new OliveException('Missing CSRFToken object');

        $out = '';
        if ($key = $csrf->getKey())
            $out .= static::hidden('_csrf_key', $key);
        return $out . static::hidden('_csrf_token', $csrf->spawnToken());
    }

    public static function hidden($name, $value = null, $attribs = []) {
        return static::input($name, $value, static::T_HIDDEN, $attribs);
    }

    public static function input($name, $value = null, $type = self::T_TEXT, $attribs = []) {

        $attribs['type']  = $type;
        $attribs['name']  = $name;
        $attribs['value'] = $value;

        return static::domElement('input', $attribs);

    }

    public static function text($name, $value = null, $attribs = []) {
        return static::input($name, $value, static::T_TEXT, $attribs);
    }

    public static function number($name, $value = null, $attribs = []) {
        return static::input($name, $value, static::T_NUMBER, $attribs);
    }

    public static function password($name, $attribs = []) {
        return static::input($name, null, static::T_PASSWORD, $attribs);
    }

    public static function range($name, $value = null, $attribs = []) {
        return static::input($name, $value, static::T_RANGE, $attribs);
    }

    public static function email($name, $value = null, $attribs = []) {
        return static::input($name, $value, static::T_EMAIL, $attribs);
    }

    public static function tel($name, $value = null, $attribs = []) {
        return static::input($name, $value, static::T_TEL, $attribs);
    }

    public static function date($name, $value = null, $attribs = []) {
        if (is_object($value)) {
            if ($value instanceof DateTime)
                $value = $value->legacyFormat('Y-m-d');
            elseif ($value instanceof \DateTime)
                $value = $value->format('Y-m-d');
        }
        return static::input($name, $value, static::T_DATE, $attribs);
    }

    public static function datetime($name, $value = null, $attribs = []) {
        if (is_object($value)) {
            if ($value instanceof DateTime)
                $value = $value->legacyFormat(DateTime::RFC3339);
            elseif ($value instanceof \DateTime)
                $value = $value->format(\DateTime::RFC3339);
        }
        return static::input($name, $value, static::T_DATETIME, $attribs);
    }

    public static function datetimeLocal($name, $value = null, $attribs = []) {
        if (is_object($value)) {
            if ($value instanceof DateTime)
                $value = $value->legacyFormat('Y-m-d\TH:i');
            elseif ($value instanceof \DateTime)
                $value = $value->format('Y-m-d\TH:i');
        }
        return static::input($name, $value, static::T_DATETIME_LOCAL, $attribs);
    }

    public static function time($name, $value = null, $attribs = []) {
        if (is_object($value)) {
            if ($value instanceof DateTime)
                $value = $value->legacyFormat('H:i');
            elseif ($value instanceof \DateTime)
                $value = $value->format('H:i');
        }
        return static::input($name, $value, static::T_TIME, $attribs);
    }

    public static function week($name, $value = null, $attribs = []) {
        if (is_object($value)) {
            if ($value instanceof DateTime)
                $value = $value->legacyFormat('Y-\WW');
            elseif ($value instanceof \DateTime)
                $value = $value->format('Y-\WW');
        }
        return static::input($name, $value, static::T_WEEK, $attribs);
    }

    public static function month($name, $value = null, $attribs = []) {
        if (is_object($value)) {
            if ($value instanceof DateTime)
                $value = $value->legacyFormat('Y-m');
            elseif ($value instanceof \DateTime)
                $value = $value->format('Y-m');
        }
        return static::input($name, $value, static::T_MONTH, $attribs);
    }


    public static function url($name, $value = null, $attribs = []) {
        return static::input($name, $value, static::T_URL, $attribs);
    }

    public static function color($name, $value = null, $attribs = []) {
        return static::input($name, $value, static::T_COLOR, $attribs);
    }

    public static function file($name, $value = null, $attribs = []) {
        return static::input($name, $value, static::T_FILE, $attribs);
    }

    public static function textarea($name, $value = null, $encode = false, $attribs = []) {
        $attribs['name'] = $name;
        return static::domElement('textarea', $attribs, $encode ? self::specialsEncode($value) : $value);
    }

    public static function select($name, $list = [], $selected = null,
                                  array $selectAttribs = [],
                                  array $optionsAttribs = [],
                                  array $optgroupsAttribs = []) {
        $html = '';
        foreach ($list as $value => $item) {
            $html .= is_array($item)
                ? static::optgroup($item, $value, $selected, $optionsAttribs[$value] ?? [], $optgroupsAttribs[$value] ?? [])
                : static::option($value, $item, $selected === $value, $optionsAttribs[$value] ?? []);
        }
        $selectAttribs['name'] = $name;
        return self::domElement('select', $selectAttribs, $html, static::TAG_NORMAL);
    }

    protected static function optgroup($list, $label, $selected = null, $optionsAtrribs = [], $attribs = []) {

        $attribs['label'] = $label;
        $g                = '';

        foreach ($list as $value => $content) {
            $g .= self::option($value, $content, $selected === $value, $optionsAtrribs[$value] ?? []);
        }

        return static::domElement('optgroup', $attribs, $g);
    }

    private static function option($value = null, $content = null, $selected = false, $attribs = []) {
        if (empty($value))
            $attribs['value'] = $value;
        if ($selected) $attribs['selected'] = 'selected';
        return static::domElement('option', $attribs, $content, static::TAG_NORMAL);
    }
    #endregion

    #region Inner helpers

    public static function checkbox($name, $value = 1, $checked = false, $attribs = []) {
        if ($checked) $attribs['checked'] = 'checked';
        return static::input($name, $value, static::T_CHECKBOX, $attribs);
    }

    public static function radio($name, $value = null, $checked = false, $attribs = []) {

        if ($checked) $attribs['checked'] = 'checked';

        return static::input($name, $value ?? $name, static::T_RADIO, $attribs);
    }
    #endregion
}
