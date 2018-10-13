<?php namespace Olive\Html;

use Olive\Http\Linker;

abstract class Html {

    #region Consts: TAG_*

    const TAG_AUTO_DETECT = 0;
    const TAG_NORMAL      = 1;
    const TAG_ENCLOSING   = 2;
    const TAG_EMPTY       = 3;

    #endregion

    #region Builders

    /**
     * @param string|array $src see: {@see \Olive\util\html::parseURL() html::parseURL}
     * @param array $attribs
     * @return string
     */
    public static function js($src, array $attribs = []) {
        $attribs['src'] = self::parseURL($src);
        return self::tag('script', $attribs, NULL, self::TAG_NORMAL);
    }

    /**
     * @param string|array $href see: {@see \Olive\util\html::parseURL() html::parseURL}
     * @param array $attribs
     * @return string
     */
    public static function css($href, array $attribs = []) {

        $attribs = array_merge([
            'media' => 'all',
            'type'  => 'text/css',
            'rel'   => 'stylesheet',
        ], $attribs);

        $attribs['href'] = self::parseURL($href);

        return self::tag('link', $attribs, NULL, self::TAG_EMPTY);
    }

    /**
     * @param string|array $src see: {@see \Olive\util\html::parseURL() html::parseURL}
     * @param string $alt
     * @param array $attribs
     * @return string
     */
    public static function img($src, $alt = NULL, array $attribs = []) {
        $attribs['alt'] = $alt;
        if($src)
            $attribs = self::parseURL($src);
        return self::tag('img', $attribs, NULL, self::TAG_EMPTY);
    }

    /**
     * @param string|array $href see: {@see \Olive\util\html::parseURL() html::parseURL}
     * @param array $attribs
     * @return string
     */
    public static function favicon($href, $attribs = NULL) {
        $attribs = array_merge(['rel' => 'shortcut icon', 'type' => 'image/x-icon'], $attribs);

        $attribs['href'] = self::parseURL($href);

        return self::tag('link', $attribs, NULL, self::TAG_EMPTY);
    }

    /**
     * @param string|array $href see: {@see \Olive\util\html::parseURL() html::parseURL}
     * @param string $content
     * @param array $attribs
     * @return string
     */
    public static function a($href, $content = NULL, array $attribs = []) {
        $attribs['href'] = self::parseURL($href);
        return self::tag('a', $attribs, self::entitiesEncode($content), self::TAG_NORMAL);
    }

    /**
     * Generates non-breaking space entities based on number supplied.
     * @param int $num
     * @return string
     */
    public static function nbsp($num = 1) {
        return str_repeat('&nbsp;', $num);
    }

    /**
     * @param string $name
     * @param string $content
     * @param array $attribs
     * @return string
     */
    public static function meta($name, $content, array $attribs = []) {
        $attribs = array_merge(['name' => $name, 'content' => $content], $attribs);
        return self::tag('meta', $attribs, NULL, self::TAG_EMPTY);
    }

    #endregion

    #region Helpers

    /**
     * ## Parse given `$url`
     * * **Strings** _returns:_ `src($url)`
     * * **Escaped strings** (strings they starts with back-slash \\) _returns:_ `substr($url, 1)`
     * * **Array** [string,bool] with 2 elements _returns:_ `src(string, bool)`
     * @param string|array $url
     * @return string
     */
    protected static function parseURL($url) {
        return Linker::parse($url);
    }

    /**
     * Convert all applicable characters to HTML entities.
     * @param string $value
     * @return string
     */
    public static function entitiesEncode($value) {
        return htmlentities($value, ENT_QUOTES, "UTF-8", FALSE);
    }

    /**
     * Convert all HTML entities to their applicable characters
     * @param string $value
     * @return string
     */
    public static function entitiesDecode($value) {
        return html_entity_decode($value, ENT_QUOTES, "UTF-8");
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
    public static function tag($name, $attrs_or_content = NULL, $content = NULL, $tagtype = self::TAG_AUTO_DETECT) {
        $attrs = '';
        if(is_array($attrs_or_content))
            foreach($attrs_or_content as $attr => $val) {
                if(is_array($val)) $val = implode(' ', $val);
                $val = self::entitiesEncode($val);
                if(is_int($attr)) $attrs .= "$val "; # prop
                else $attrs .= "$attr=\"$val\" ";
            }
        elseif(is_string($attrs_or_content))
            $content = $attrs_or_content;

        $attrs = mb_substr($attrs, 0, -1);
        if($tagtype == self::TAG_AUTO_DETECT) {
            $ttags   = [
                'input' => self::TAG_EMPTY,
                'br'    => self::TAG_EMPTY,
                'hr'    => self::TAG_EMPTY,
                'meta'  => self::TAG_EMPTY,
                'link'  => self::TAG_EMPTY,
                'img'   => self::TAG_EMPTY,
            ];
            $tagtype = isset($ttags[($lowname = strtolower($name))]) ? $ttags[$lowname] : self::TAG_NORMAL;
        }
        switch($tagtype) {
            case self::TAG_EMPTY:
                return "<$name" . ($attrs ? ' ' : '') . "$attrs>";
                break;
            case self::TAG_ENCLOSING:
                return "<$name" . ($attrs ? ' ' : '') . "$attrs/>";
                break;
            default:
            case self::TAG_NORMAL:
                return "<$name" . ($attrs ? ' ' : '') . "$attrs>$content</$name>";
                break;
        }
    }

    #endregion

}