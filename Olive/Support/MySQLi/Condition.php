<?php namespace Olive\Support\MySQLi;

use Olive\Exceptions\MySQLiConditionException;

class Condition
{

    #region Combiners
    public const AND    = 'AND';
    public const OR     = 'OR';
    public const ANDNOT = 'AND NOT';
    public const ORNOT  = 'OR NOT';
    #endregion

    #region Operators
    public const equal              = '=';
    public const notEqual           = '<>';
    public const greaterThan        = '>';
    public const greaterThanOrEqual = '>=';
    public const lessThan           = '<';
    public const lessThanOrEqual    = '<=';
    public const is                 = 'IS';
    public const isNot              = 'IS NOT';
    public const in                 = 'IN';
    public const notIn              = 'NOT IN';
    public const not                = 'NOT';
    public const like               = 'LIKE';
    public const notLike            = 'NOT LIKE';
    public const between            = 'BETWEEN';
    public const notBetween         = 'NOT BETWEEN';
    #endregion

    #region Fields
    private static $dbConnection;
    private        $conditions = [];
    #endregion

    #region Constructor

    /**
     * Condition constructor.
     * @param mixed $args
     * @throws MySQLiConditionException
     */
    public function __construct(...$args) {
        if (self::$dbConnection === null)
            self::$dbConnection = MySQLiConnection::getInstance();
        $this->append($args, self:: AND);
    }
    #endregion

    #region Appenders
    /**
     * @param $args
     * @param $combiner
     * @throws MySQLiConditionException
     */
    protected function append($args, $combiner) {
        $argsCount = count($args);
        if ($argsCount === 0) return;
        switch ($argsCount) {
            case 1: # array: condition, string: raw, Condition: complex
                if (is_array($args[0]) || is_string($args[0]) || $args[0] instanceof self)
                    $this->conditions[] = [$combiner, static::parseArg($args[0])];
                else
                    throw new MySQLiConditionException('Unknown format');
                break;
            case 2: # field=val
                $this->conditions[] = [$combiner, self::parseArg([$args[0] => $args[1]])];
                break;
            case 3:
            default:
                $this->conditions[] = [$combiner, self::parseArg([[$args[0], $args[1], $args[2]]])];
                break;
        }
    }

    /**
     * @param $arg
     * @return string|null
     * @throws MySQLiConditionException
     */
    protected static function parseArg($arg) {

        if (is_array($arg)) {
            $raw = '';
            foreach ($arg as $n => $v) {
                if (is_array($v)) {
                    //advanced condition
                    $n = $v[0];
                    if (strpos($n, '`') === false)
                        $n = "`$n`";
                    $operator = strtoupper($v[1]);
                    $value    = $v[2];

                    $secure_value = false;
                    switch ($operator) {
                        case self::like:
                        case self::notLike:
                            if (!$value)
                                continue 2;
                            if (is_array($value)) {
                                $value        = implode(" OR $n $operator ", self::val($value));
                                $secure_value = true;
                            }
                            break;
                        case self::in:
                        case self::notIn:
                            if (!is_array($value))
                                throw new MySQLiConditionException('The parameter passed to IN operator must be array.');
                            $value        = '(' . implode(',', self::val($value)) . ')';
                            $secure_value = true;
                            break;
                        default:
                            break;
                    }
                    if (!$secure_value)
                        $value = self::val($value);
                    $raw .= ($raw === '' ? '' : ' AND ') . "$n $operator $value";
                } elseif (is_int($n)) {
                    //direct string condition
                    $raw .= ($raw === '' ? '' : ' AND ') . $v;
                } else {
                    //simple condition
                    if (strpos($n, '`') === false)
                        $n = "`$n`";
                    if ($v === null)
                        $c = "$n IS NULL";
                    else {
                        $v = self::$dbConnection->escape_string($v);
                        $c = "$n='$v'";
                    }
                    $raw .= ($raw === '' ? '' : ' AND ') . $c;
                }
            }
            return $raw;
        } elseif (is_string($arg))
            return (string)$arg;
        elseif ($arg instanceof self) {
            $arg = $arg->parse();
            if ($arg === '') return null;
            return "($arg)";
        }
        return null;
    }

    /**
     * @param $value
     * @return array|string
     */
    protected static function val($value) {
        return self::$dbConnection->val($value);
    }

    public function parse() {
        $raw = '';
        $pos = 0;
        foreach ($this->conditions as $cond) {
            if ($pos !== 0) # first condition's combiner should be ignored
                $raw .= ") $cond[0] (";
            $raw .= $cond[1];
            $pos++;
        }
        if (count($this->conditions) > 1) return
            $raw !== '' ? "($raw)" : '';
        return $raw;
    }
    #endregion

    #region Protected methods

    /**
     * @param mixed ...$args
     * @return Condition
     * @throws MySQLiConditionException
     */
    public static function where(...$args) {
        return new static(...$args);
    }

    /**
     * @param mixed ...$args
     * @return $this
     * @throws MySQLiConditionException
     */
    public function and(...$args) {
        $this->append($args, self:: AND);
        return $this;
    }

    /**
     * @param mixed ...$args
     * @return $this
     * @throws MySQLiConditionException
     */
    public function or(...$args) {
        $this->append($args, self:: OR);
        return $this;
    }
    #endregion

    #region Parsers

    /**
     * @param mixed ...$args
     * @return $this
     * @throws MySQLiConditionException
     */
    public function andNot(...$args) {
        $this->append($args, self::ANDNOT);
        return $this;
    }

    /**
     * @param mixed ...$args
     * @return $this
     * @throws MySQLiConditionException
     */
    public function orNot(...$args) {
        $this->append($args, self::ORNOT);
        return $this;
    }
    #endregion

    #region Helpers

    public function __toString() {
        return $this->parse();
    }
    #endregion
}
