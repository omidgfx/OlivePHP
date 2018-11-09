<?php namespace Olive\Util;


class TimeLapse {
    public $y, $m, $w, $d, $h, $i, $s;
    private                        $mode;

    private static $formatters = null;

    /**
     * TimeLapse constructor.
     * @param int|\DateTime $diffTo
     * @param int|\DateTime $date
     */
    public function __construct($diffTo, $date) {

        if($diffTo instanceof \DateTime)
            $diffToDate = $diffTo;
        else {
            $diffToDate = new \DateTime;
            if(is_numeric($diffTo)) {
                $diffToDate = new \DateTime;
                $diffToDate->setTimestamp($diffTo);
            }
        }

        if($date instanceof \DateTime)
            $tdate = $date;
        else {
            $tdate = new \DateTime;
            if(is_numeric($date)) {
                $tdate = new \DateTime;
                $tdate->setTimestamp($date);
            }
        }

        $diff = $tdate->diff($diffToDate);

        $diff->{'w'} = floor($diff->d / 7);
        $diff->d     -= $diff->{'w'} * 7;

        foreach(['y', 'm', 'w', 'd', 'h', 'i', 's'] as $item)
            $this->$item = $diff->$item;


        $f = $diffToDate->getTimestamp();
        $t = $tdate->getTimestamp();
        if($f > $t)
            $this->mode = 1;//future
        elseif($t > $f)
            $this->mode = -1;//past
        else
            $this->mode = 0;//sametime
    }

    private static function setFormatters() {
        self::$formatters['en_US'] = function($full, TimeLapse $lapse) {
            // Google Translate
            //'hence':
            // in the future (used after a period of time).
            // "two years hence they might say something quite different"
            $strings = ['y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second'];
            $s       = function($k, $val) use ($strings) {
                if($val > 1) return $val . ' ' . $strings[$k] . 's';
                return 'a' . ($k == 'h' ? 'n ' : ' ') . $strings[$k];
            };
            foreach($strings as $k => &$v) {
                if($lapse->$k) {
                    $v = $s($k, $lapse->$k);
                } else {
                    unset($strings[$k]);
                }
            }

            if(!$full) $strings = array_slice($strings, 0, 1);

            if($strings) {
                $out = implode(' and ', $strings);
                if($lapse->mode == 1)
                    $out .= ' hence';
                elseif($lapse->mode == -1)
                    $out .= ' ago';
                else $out = 'just now';
                return $out;
            } else return 'just now';
        };
        self::$formatters['en']    = &self::$formatters['en_US'];

        self::$formatters['fa_IR'] = function($full, TimeLapse $lapse) {
            $strings = ['y' => 'سال', 'm' => 'ماه', 'w' => 'هفته', 'd' => 'روز', 'h' => 'ساعت', 'i' => 'دقیقه', 's' => 'ثانیه'];
            $s       = function($k, $val) use ($strings) {
                if($val > 1) return $val . ' ' . $strings[$k];
                return 'یک ' . $strings[$k];
            };
            foreach($strings as $k => &$v) {
                if($lapse->$k) {
                    $v = $s($k, $lapse->$k);
                } else {
                    unset($strings[$k]);
                }
            }

            if(!$full) $strings = array_slice($strings, 0, 1);

            if($strings) {
                $out = implode(' و ', $strings);
                if($lapse->mode == 1)
                    $out .= ' دیگر';
                elseif($lapse->mode == -1)
                    $out .= ' پیش';
                else $out = 'همین حالا';
                return Digit::en2fa($out);
            } else return 'همین حالا';
        };

        self::$formatters['fa'] = &self::$formatters['fa_IR'];
    }

    /**
     * @param bool $full
     * @param string $locale
     * @return mixed
     */
    public function format($full = true, $locale = 'en_US') {
        if(self::$formatters == null)
            self::setFormatters();

        # Lowercase first 2 letters
        $locale = strtolower(substr($locale, 0, 2)) . substr($locale, 2);

        if(!key_exists($locale, self::$formatters))
            $locale = 'en_US';

        $fn = self::$formatters[$locale];
        return $fn($full, $this);
    }

}