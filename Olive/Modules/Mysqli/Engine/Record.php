<?php namespace Olive\MySQLi;

use Olive\Exceptions\MySQLiAdaptingException;
use Olive\Exceptions\MySQLiConditionException;
use Olive\Exceptions\MySQLiException;
use Olive\Exceptions\MySQLiRecordException;
use Olive\manifest;
use Olive\Util\DateTime;

/**
 * @property $id
 * Class Record
 * @package Olive\MySQLi
 */
abstract class Record implements RecordInterface {

    #region Changings
    private $_ORIGS = [];
    private $_CHNGS = [];

    public function __set($name, $value) {
        $this->_CHNGS[$name] = $value;
    }

    public function __get($name) {
        return isset($this->_CHNGS[$name])
            ? $this->_CHNGS[$name]
            : (
            isset($this->_ORIGS[$name])
                ? $this->_ORIGS[$name]
                : NULL
            );
    }

    public function __clone() {
        $this->unset('id');
        $this->htmlSpecialCharsDecode()->syncChanged();
    }

    /**
     * @internal
     * Sync seted changes as original record that is stored in databse.<br><b>This method runs automatically on fetching instace of Record.</b>
     * @return $this
     */
    public function syncOriginal() {
        $this->_ORIGS = $this->toArray();
        $this->_CHNGS = [];
        return $this;
    }

    public function syncChanged() {
        $this->_CHNGS = $this->_ORIGS;
        $this->_ORIGS = [];
        return $this;
    }

    public function getChanges() {
        return $this->_CHNGS;
    }

    /**
     * @param string $name <b>NULL</b>: reset all original
     * @return $this
     */
    public function undo($name = NULL) {
        if($name) unset($this->_CHNGS[$name]);
        else $this->_CHNGS = [];
        return $this;
    }

    /**
     * @param string|NULL $name
     * @return $this
     */
    public function unset(string $name = NULL) {
        if($name) unset($this->_ORIGS[$name], $this->_CHNGS[$name]);
        else $this->_CHNGS = $this->_ORIGS = [];
        return $this;
    }

    #endregion

    #region Public methods
    public function toArray() {
        return array_merge($this->_ORIGS, $this->_CHNGS);
    }
    #endregion

    #region DB related methods
    /**
     * @param array|string|Condition $condition
     * @param bool $single
     * @param int[]|int $limit
     * @param string|string[] $columns
     * @param string $orderby
     * @return static|static[]
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public static function select($condition = NULL, $single = FALSE, $limit = NULL, $columns = NULL, $orderby = NULL) {
        if($single)
            return DB::getInstance()->selectRecord(static::class, $condition, $limit, $columns, $orderby);
        return DB::getInstance()->selectRecords(static::class, $condition, $limit, $columns, $orderby);
    }

    /**
     * @param int $id
     * @return Record A Model or a View by id
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public static function selectById($id) {
        return self::select(new Condition('id', $id), TRUE);
    }

    /**
     * @param array|string|Condition $condition
     * @return int Count of rows in table based on condition
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     * @see DB::count()
     */
    public static function count($condition = NULL) {
        return DB::getInstance()->count(static::table(), $condition);
    }

    /**
     * @param string|array|Condition $condition
     * @return bool
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public static function exists($condition = NULL) {
        # db
        $db = DB::getInstance();

        # execute
        return $db->exists(static::table(), $condition);
    }

    /**
     * @return $this A fresh instance of current record based on id
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     * @throws MySQLiRecordException
     */
    public function pull() {
        if($this->id == NULL)
            throw new MySQLiRecordException('Invalid id');
        $new          = static::selectById($this->id);
        $this->_ORIGS = $new->_ORIGS;
        $this->_CHNGS = $new->_CHNGS;

        return $this;
    }

    /**
     * @param string $column
     * @param array $options Option Keys:<br>
     * <b>calendar</b>: persian, gregorian, islamic<br>
     * <b>locale</b>: fa_IR, en_US, ...<br>
     * <b>timezone</b>: A {@see \DateTimeZone DateTimeZone} or a string
     * @param string|null $pattern <b>Null</b> returns an instance of {@see \Olive\Util\DateTime Olive DateTime}<br>
     * <b>string</b> returns a formatted date-time string. Read more about {@see \Olive\Util\DateTime::format pattern syntax}
     * @param mixed $fallback
     * @uses \Olive\Util\DateTime
     * @return null|DateTime|string
     */
    public function getDateTime($column = 'date', $options = [], $pattern = manifest::DEFAULT_DATETIME_PATTERN_SHORT, $fallback = NULL) {
        if($this->{$column} == NULL)
            return $fallback;

        # default option
        $defaults = [
            'calendar' => manifest::DEFAULT_CALENDAR,
            'locale'   => manifest::DEFAULT_DATETIME_LOCALE,
            'timezone' => manifest::DEFAULT_TIMEZONE,
        ];
        $options  = array_merge($defaults, $options);

        $d = new DateTime;
        $d->setTimestamp($this->{$column});

        $d->setCalendar($options['calendar']);
        $d->setLocale($options['locale']);
        $d->setTimezone($options['timezone']);

        return $pattern ? $d->format($pattern) : $d;
    }

    /**
     * @param string $column
     * @return string[]
     * @throws MySQLiAdaptingException
     * @throws MySQLiException
     */
    public function columnEnums($column) {
        return DB::getInstance()->columnEnums(static::table(), $column);
    }

    #endregion

    #region Helpers
    /**
     * @param string $column
     * @param Record[]|static[] $records
     * @return Record[]|static[] An associated array based on column
     */
    public static function associateBy($column, $records) {
        $arr = [];
        foreach($records as $model) {
            $arr[$model->{$column}] = $model;
        }
        return $arr;
    }

    /**
     * @param string $column
     * @param Record[]|static[] $records
     * @param string $association
     * @return array|Record[]|static[] An array of all record's column values. if association parameter was a name of a column then returns an associated array with same values
     */
    public static function extract($column, $records, $association = NULL) {
        $arr = [];

        if($association == NULL)
            foreach($records as $record)
                $arr[] = $record->{$column};
        else
            foreach($records as $record)
                $arr[$record->{$association}] = $record->{$column};

        return $arr;
    }

    #endregion

    #region Escaping _ORIG
    public function htmlSpecialCharsEncode($origs = TRUE) {
        if($origs) foreach($this->_ORIGS as &$v)
            $v = htmlspecialchars($v);
        else foreach($this->_CHNGS as &$v)
            $v = htmlspecialchars($v);
        return $this;
    }

    public function htmlSpecialCharsDecode($origs = TRUE) {
        if($origs) foreach($this->_ORIGS as &$v)
            $v = htmlspecialchars_decode($v);
        else foreach($this->_CHNGS as &$v)
            $v = htmlspecialchars_decode($v);
        return $this;
    }
    #endregion

}