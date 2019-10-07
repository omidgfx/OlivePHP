<?php namespace Olive\Util;

use DateTime as NativeDateTime;
use DateTimeZone;
use Exception;
use IntlDateFormatter;
use NumberFormatter;

/**
 * DateTime is an extended version of php 5 DateTime class with integrated
 * IntlDateFormatter functionality which adds support for multiple calendars
 * and locales provided by ICU project. (needs php >= 5.3.0 with intl extension)
 * However, this class is not compatible with DateTime class because it uses ICU
 * pattern syntax for formatting and parsing date strings.
 * (@link http://userguide.icu-project.org/formatparse/datetime)
 *
 * @copyright   Copyright 2010, Ali Farhadi (http://farhadi.ir/)
 * @license     GNU General Public License 3.0 (http://www.gnu.org/licenses/gpl.html)
 */
class DateTime extends NativeDateTime
{

    /**
     * @var string The current locale in use
     */
    protected $locale;
    /**
     * @var string The current calendar in use
     */
    protected $calendar;

    /**
     * Creates a new instance of DateTime
     *
     * @param mixed $time Unix timestamp or strtotime() compatible string or another DateTime object
     * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
     * @param string $calendar any calendar supported by ICU (e.g. gregorian, persian, islamic, ...)
     * @param string $locale any locale supported by ICU
     * @param string $pattern the date pattern in which $time is formatted.
     * @throws Exception
     */
    public function __construct($time = null, $timezone = null, $calendar = 'gregorian', $locale = 'en_US', $pattern = null) {
        if (!isset($timezone)) $timezone = new DateTimeZone(date_default_timezone_get());
        elseif (!$timezone instanceof DateTimeZone)
            $timezone = new DateTimeZone($timezone);
        parent::__construct(null, $timezone);
        $this->setLocale($locale);
        $this->setCalendar($calendar);
        if (isset($time)) $this->set($time, null, $pattern);
    }

    /**
     * Alters object's internal timestamp with a string acceptable by strtotime() or a Unix timestamp or a DateTime object.
     *
     * @param mixed $time Unix timestamp or strtotime() compatible string or another DateTime object
     * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
     * @param string $pattern the date pattern in which $time is formatted.
     * @return DateTime The modified DateTime.
     */
    public function set($time, $timezone = null, $pattern = null) {
        if ($time instanceof NativeDateTime) {
            $time = $time->format('U');
        } elseif (!is_numeric($time) || $pattern) {
            if (!$pattern) {
                $pattern = $this->guessPattern($time);
            }
            if (!$pattern && preg_match('/((?:[+-]?\d+)|next|last|previous)\s*(year|month)s?/i', $time)) {
                $tempTimezone = null;
                if (isset($timezone)) {
                    $tempTimezone = $this->getTimezone();
                    $this->setTimezone($timezone);
                }
                $this->setTimestamp(time());
                $this->modify($time);
                if (isset($timezone)) {
                    $this->setTimezone($tempTimezone);
                }
                return $this;
            }
            $timezone = empty($timezone) ? $this->getTimezone() : $timezone;
            if ($timezone instanceof DateTimeZone) $timezone = $timezone->getName();
            $defaultTimezone = @date_default_timezone_get();
            date_default_timezone_set($timezone);
            if ($pattern) {
                $time = $this->getFormatter(['timezone' => 'GMT', 'pattern' => $pattern])->parse($time);
                $time -= date('Z', $time);
            } else {
                $time = strtotime($time);
            }
            date_default_timezone_set($defaultTimezone);
        }
        $this->setTimestamp($time);
        return $this;
    }

    /**
     * Tries to guess the date pattern in which $time is formatted.
     *
     * @param string $time The date string
     * @return string Detected ICU pattern on success, FALSE otherwise.
     */
    protected function guessPattern($time) {
        $time           = $this->latinizeDigits(trim($time));
        $shortDateRegex = '(\d{2,4})(-|\\\\|/)\d{1,2}\2\d{1,2}';
        $longDateRegex  = '([^\d]*\s)?\d{1,2}(-| )[^-\s\d]+\4(\d{2,4})';
        $timeRegex      = '\d{1,2}:\d{1,2}(:\d{1,2})?(\s.*)?';
        /** @noinspection all */
        if (preg_match("@^(?:(?:$shortDateRegex)|(?:$longDateRegex))(\s+$timeRegex)?$@", $time, $match)) {
            if (!empty($match[1])) {
                $separator = $match[2];
                $pattern   = strlen($match[1]) === 2 ? 'yy' : 'yyyy';
                $pattern   .= $separator . 'MM' . $separator . 'dd';
            } else {
                $separator = $match[4];
                $pattern   = 'dd' . $separator . 'LLL' . $separator;
                $pattern   .= strlen($match[5]) === 2 ? 'yy' : 'yyyy';
                if (!empty($match[3])) $pattern = (preg_match('/,\s+$/', $match[3]) ? 'E, ' : 'E ') . $pattern;
            }
            if (!empty($match[6])) {
                $pattern .= !empty($match[8]) ? ' hh:mm' : ' HH:mm';
                if (!empty($match[7])) $pattern .= ':ss';
                if (!empty($match[8])) $pattern .= ' a';
            }
            return $pattern;
        }
        return false;
    }

    /**
     * Replaces localized digits in $str with latin digits.
     *
     * @param string $str
     * @return string Latinized string
     */
    protected function latinizeDigits($str) {
        $result = '';
        $num    = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
        preg_match_all('/.[\x80-\xBF]*/', $str, $matches);
        foreach ($matches[0] as $char) {
            $pos        = 0;
            $parsedChar = $num->parse($char, NumberFormatter::TYPE_INT32, $pos);
            $result     .= $pos ? $parsedChar : $char;
        }
        return $result;
    }

    /**
     * Sets the timezone for the object.
     *
     * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
     * @return DateTime The modified DateTime.
     */
    public function setTimezone($timezone) {
        if (!$timezone instanceof DateTimeZone) $timezone = new DateTimeZone($timezone);
        parent::setTimezone($timezone);
        return $this;
    }

    /**
     * Overrides the setTimestamp method to support timestamps out of the integer range.
     *
     * @param float $unixtimestamp Unix timestamp representing the date.
     * @return DateTime the modified DateTime.
     */
    public function setTimestamp($unixtimestamp) {
        $diff     = $unixtimestamp - $this->getTimestamp();
        $days     = floor($diff / 86400);
        $seconds  = $diff - $days * 86400;
        $timezone = $this->getTimezone();
        $this->setTimezone('UTC');
        parent::modify("$days days $seconds seconds");
        $this->setTimezone($timezone);
        return $this;
    }

    /**
     * Overrides the getTimestamp method to support timestamps out of the integer range.
     *
     * @return float Unix timestamp representing the date.
     */
    public function getTimestamp() {
        return (float)parent::format('U');
    }

    /**
     * Alter the timestamp by incrementing or decrementing in a format accepted by strtotime().
     *
     * @param string $modify a string in a relative format accepted by strtotime().
     * @return DateTime The modified DateTime.
     */
    public function modify($modify) {
        $modify = $this->latinizeDigits(trim($modify));
        $modify = preg_replace_callback('/(.*?)((?:[+-]?\d+)|next|last|previous)\s*(year|month)s?/i', [$this, 'modifyCallback'], $modify);
        if ($modify) parent::modify($modify);
        return $this;
    }

    /**
     * Returns an instance of IntlDateFormatter with specified options.
     *
     * @param array $options
     * @return IntlDateFormatter
     */
    protected function getFormatter($options = []) {
        $locale   = empty($options['locale']) ? $this->locale : $options['locale'];
        $calendar = empty($options['calendar']) ? $this->calendar : $options['calendar'];
        $timezone = empty($options['timezone']) ? $this->getTimezone() : $options['timezone'];
        if ($timezone instanceof DateTimeZone) $timezone = $timezone->getName();
        $pattern = empty($options['pattern']) ? null : $options['pattern'];
        return new IntlDateFormatter($locale . '@calendar=' . $calendar,
            IntlDateFormatter::FULL, IntlDateFormatter::FULL, $timezone,
            $calendar === 'gregorian' ? IntlDateFormatter::GREGORIAN : IntlDateFormatter::TRADITIONAL, $pattern);
    }

    /**
     *
     * @param bool|false $full
     * @return string
     * @throws Exception
     */
    public function toTimeLaps($full = true) {
        $lapse = new TimeLapse($this, null);
        return $lapse->format($full, $this->locale);
    }

    /**
     * Gets the current locale used by the object.
     *
     * @return string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Sets the locale used by the object.
     *
     * @param string $locale
     * @return DateTime
     */
    public function setLocale($locale) {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Gets the current calendar used by the object.
     *
     * @return string
     */
    public function getCalendar() {
        return $this->calendar;
    }

    /**
     * Sets the calendar used by the object.
     *
     * @param string $calendar
     * @return DateTime The modified DateTime.
     */
    public function setCalendar($calendar) {
        $this->calendar = strtolower($calendar);
        return $this;
    }

    /**
     * Preserve original DateTime::format functionality
     *
     * @param string $format Format accepted by date().
     * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
     * @return string Formatted date on success or FALSE on failure.
     */
    public function legacyFormat($format, $timezone = null) {
        if ($timezone !== null) {
            $tempTimezone = $this->getTimezone();
            $this->setTimezone($timezone);
            $result = parent::format($format);
            $this->setTimezone($tempTimezone);
            return $result;
        }
        return parent::format($format);
    }

    /**
     * Internally used by modify method to calculate calendar-aware modifications
     *
     * @param array $matches
     * @return string An empty string
     */
    protected function modifyCallback($matches) {
        if (!empty($matches[1])) {
            parent::modify($matches[1]);
        }
        [$y, $m, $d] = explode('-', $this->format('y-M-d'));
        $change = strtolower($matches[2]);
        $unit   = strtolower($matches[3]);
        switch ($change) {
            case 'next':
                $change = 1;
                break;
            case 'last':
            case 'previous':
                $change = -1;
                break;
        }
        switch ($unit) {
            case 'month':
                $m += $change;
                if ($m > 12) {
                    $y += floor($m / 12);
                    $m %= 12;
                } elseif ($m < 1) {
                    $y += ceil($m / 12) - 1;
                    $m = $m % 12 + 12;
                }
                break;
            case 'year':
                $y += $change;
                break;
        }
        $this->setDate($y, $m, $d);
        return '';
    }

    /**
     * Returns date formatted according to given pattern.
     *
     * @param string $pattern Date pattern in ICU syntax @link http://userguide.icu-project.org/formatparse/datetime
     * <table style="border-collapse: collapse;" cellpadding="2" border="1"><tbody><tr style="border:1px solid"><th style="text-align: center; width: 630px; height: 49px;" colspan="4">Date Field Symbol Table</th></tr><tr style="border:1px solid"><th>Symbol</th><th>Meaning</th><th colspan="2">Example(s)</th></tr><tr style="border:1px solid"><td>G</td><td>era designator</td><td>G, GG, <em>or</em> GGG<br> GGGG<br> GGGGG</td><td>AD<br> Anno Domini<br> A</td></tr><tr style="border:1px solid"><td>y</td><td>year</td><td>yy<br> y <em>or</em> yyyy</td><td>96<br> 1996</td></tr><tr style="border:1px solid"><td>Y</td><td>year of "Week of Year"</td><td>Y</td><td>1997</td></tr><tr style="border:1px solid"><td>u</td><td>extended year</td><td>u</td><td>4601</td></tr><tr style="border:1px solid"><td>U</td><td>cyclic year name, as in Chinese lunar calendar</td><td>U</td><td>甲子</td></tr><tr style="border:1px solid"><td>r</td><td>related Gregorian year</td><td>r</td><td>1996</td></tr><tr style="border:1px solid"><td>Q</td><td>quarter</td><td>Q<br>QQ<br> QQQ<br> QQQQ<br> QQQQQ</td><td>2<br>02<br> Q2<br> 2nd quarter<br> 2</td></tr><tr style="border:1px solid"><td>q</td><td><strong>Stand Alone</strong> quarter</td><td>q<em><br></em>qq<br> qqq<br> qqqq<br> qqqqq</td><td>2<br>02<br> Q2<br> 2nd quarter<br> 2</td></tr><tr style="border:1px solid"><td>M</td><td>month in year</td><td>M<br>MM<br> MMM<br> MMMM<br> MMMMM</td><td>9<br>09<br> Sep<br> September<br> S</td></tr><tr style="border:1px solid"><td>L</td><td>Stand Alone month in year</td><td>L<br>LL<br> LLL<br> LLLL<br> LLLLL</td><td>9<br>09<br> Sep<br> September<br> S</td></tr><tr style="border:1px solid"><td>w</td><td>week of year</td><td>w<br>ww</td><td>27<br>27</td></tr><tr style="border:1px solid"><td>W\</td><td>week of month</td><td>W</td><td>2</td></tr><tr style="border:1px solid"><td>d</td><td>day in month</td><td>d<br> dd</td><td>2<br> 02</td></tr><tr style="border:1px solid"><td>D</td><td>day of year</td><td>D</td><td>189</td></tr><tr style="border:1px solid"><td>F</td><td>day of week in month</td><td>F</td><td>2 (2nd Wed in July)</td></tr><tr style="border:1px solid"><td>g</td><td>modified julian day</td><td>g</td><td>2451334</td></tr><tr style="border:1px solid"><td>E</td><td>day of week</td><td>E, EE, <em>or</em> EEE<br> EEEE<br> EEEEE<br> EEEEEE</td><td>Tue<br> Tuesday<br> T<br> Tu</td></tr><tr style="border:1px solid"><td>e</td><td>local day of week<br> example: if Monday is 1st day, Tuesday is 2nd )</td><td>e <em>or </em>ee<br> eee<br> eeee<br> eeeee<br> eeeeee</td><td>2<br> Tue<br> Tuesday<br> T<br> Tu</td></tr><tr style="border:1px solid"><td>c</td><td>Stand Alone local day of week</td><td>c <em>or </em>cc<br> ccc<br> cccc<br> ccccc<br> cccccc</td><td>2<br> Tue<br> Tuesday<br> T<br> Tu</td></tr><tr style="border:1px solid"><td>a</td><td>am/pm marker</td><td>a</td><td>pm</td></tr><tr style="border:1px solid"><td>h</td><td>hour in am/pm (1~12)</td><td>h<br> hh</td><td>7<br> 07</td></tr><tr style="border:1px solid"><td>H</td><td>hour in day (0~23)</td><td>H<br> HH</td><td>0<br> 00</td></tr><tr style="border:1px solid"><td>k</td><td>hour in day (1~24)</td><td>k<br> kk</td><td>24<br> 24</td></tr><tr style="border:1px solid"><td>K</td><td>hour in am/pm (0~11)</td><td>K<br> KK</td><td>0<br> 00</td></tr><tr style="border:1px solid"><td>m</td><td>minute in hour</td><td>m<br> mm</td><td>4<br> 04</td></tr><tr style="border:1px solid"><td>s</td><td>second in minute</td><td>s<br> ss</td><td>5<br> 05</td></tr><tr style="border:1px solid"><td>S</td><td>fractional second - truncates (like other time fields) <br>to the count of letters when formatting. Appends <br>zeros if more than 3 letters specified. Truncates at <br>three significant digits when parsing.&nbsp;</td><td>S<br> SS<br> SSS<br> SSSS</td><td>2<br> 23<br> 235<br> 2350</td></tr><tr style="border:1px solid"><td>A</td><td>milliseconds in day</td><td>A</td><td>61201235</td></tr><tr style="border:1px solid"><td>z</td><td>Time Zone: specific non-location</td><td>z, zz, <em>or</em> zzz<br> zzzz</td><td>PDT<br> Pacific Daylight Time</td></tr><tr style="border:1px solid"><td>Z</td><td>Time Zone: ISO8601 basic hms? / RFC 822<br> Time Zone: long localized GMT (=OOOO)<br> TIme Zone: ISO8601 extended hms? (=XXXXX)</td><td>Z, ZZ, <em>or</em> ZZZ<br> ZZZZ<br> ZZZZZ</td><td>-0800<br> GMT-08:00<br> -08:00, -07:52:58, Z</td></tr><tr style="border:1px solid"><td>O</td><td>Time Zone: short localized GMT<br> Time Zone: long localized GMT (=ZZZZ)</td><td>O<br> OOOO</td><td>GMT-8<br> GMT-08:00</td></tr><tr style="border:1px solid"><td>v</td><td>Time Zone: generic non-location<br> (falls back first to VVVV)</td><td>v<br> vvvv</td><td>PT<br> Pacific Time <em>or</em> Los Angeles Time</td></tr><tr style="border:1px solid"><td>V</td><td>Time Zone: short time zone ID<br> Time Zone: long time zone ID<br> Time Zone: time zone exemplar city<br> Time Zone: generic location (falls back to OOOO)</td><td>V<br> VV<br> VVV<br> VVVV</td><td>uslax<br> America/Los_Angeles<br> Los Angeles<br> Los Angeles Time</td></tr><tr style="border:1px solid"><td>X</td><td>Time Zone: ISO8601 basic hm?, with Z for 0<br> Time Zone: ISO8601 basic hm, with Z<br> Time Zone: ISO8601 extended hm, with Z<br> Time Zone: ISO8601 basic hms?, with Z<br> Time Zone: ISO8601 extended hms?, with Z</td><td>X<br> XX<br> XXX<br> XXXX<br> XXXXX</td><td>-08, +0530, Z<br> -0800, Z<br> -08:00, Z<br> -0800, -075258, Z<br> -08:00, -07:52:58, Z</td></tr><tr style="border:1px solid"><td>x</td><td>Time Zone: ISO8601 basic hm?, without Z for 0<br> Time Zone: ISO8601 basic hm, without Z<br> Time Zone: ISO8601 extended hm, without Z<br> Time Zone: ISO8601 basic hms?, without Z<br> Time Zone: ISO8601 extended hms?, without Z</td><td>x<br> xx<br> xxx<br> xxxx<br> xxxxx</td><td>-08, +0530<br> -0800<br> -08:00<br> -0800, -075258<br> -08:00, -07:52:58</td></tr><tr style="border:1px solid"><td>'</td><td>escape for text</td><td>'</td><td>(nothing)</td></tr><tr style="border:1px solid"><td>' '</td><td>two single quotes produce one</td><td>' '</td><td>'</td></tr></tbody></table>
     * @param mixed $timezone DateTimeZone object or timezone identifier as full name (e.g. Asia/Tehran) or abbreviation (e.g. IRDT).
     * @return string Formatted date on success or FALSE on failure.
     */
    public function format($pattern, $timezone = null) {
        $tempTimezone = null;
        if ($timezone !== null) {
            $tempTimezone = $this->getTimezone();
            $this->setTimezone($timezone);
        }
        // Timezones DST data in ICU are not as accurate as PHP.
        // So we get timezone offset from php and pass it to ICU.
        $result = $this->getFormatter([
            'timezone' => 'GMT' . (parent::format('Z') ? parent::format('P') : ''),
            'pattern'  => $pattern,
        ])->format($this->getTimestamp());
        if ($timezone !== null) {
            $this->setTimezone($tempTimezone);
        }
        return $result;
    }

    /**
     * Resets the current date of the object.
     *
     * @param integer $year
     * @param integer $month
     * @param integer $day
     * @return DateTime The modified DateTime.
     */
    public function setDate($year, $month, $day) {
        $this->set("$year/$month/$day " . $this->format('HH:mm:ss'), null, 'yyyy/MM/dd HH:mm:ss');
        return $this;
    }
}
