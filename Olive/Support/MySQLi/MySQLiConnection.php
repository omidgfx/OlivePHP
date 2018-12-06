<?php namespace Olive\Support\MySQLi;

use Olive\Exceptions\MySQLiAdaptingException;
use Olive\Exceptions\MySQLiException;
use Olive\manifest;
use Olive\Traits\Singleton;

class MySQLiConnection extends \mysqli {
    use Singleton;

    #region Constructors
    protected function __singleton() {

        # Connect
        @$this->connect(
            manifest::MYSQLI_HOST,
            manifest::MYSQLI_USERNAME,
            manifest::MYSQLI_PASSWORD,
            manifest::MYSQLI_DBNAME
        );

        $this->query("set names 'utf8mb4'");
        $this->set_charset('UTF-8');

        if(!$this->select_db(manifest::MYSQLI_DBNAME))
            throw new MySQLiException($this->error, $this->errno);

    }
    #endregion

    #region Helper methods
    /**
     * @param $value
     * @return array|string
     */
    public function val($value) {
        if(is_array($value)) {
            return array_map(function($vns) {
                return self::val($vns);
            }, $value);
        }
        if(is_null($value))
            return 'NULL';
        elseif(is_bool($value)) {
            return $value === true ? "1" : "0";
        } elseif(is_numeric($value))
            return "'" . strval($value) . "'";
        else
            return "'" . $this->escape_string(mb_convert_encoding($value, 'utf-8')) . "'";
    }
    #endregion

    #region Overrides
    /**
     * @param string $query
     * @param int $resultmode
     * @return \mysqli_result
     * @throws MySQLiException
     */
    public function query($query, $resultmode = MYSQLI_STORE_RESULT) {
        $r = parent::query($query, $resultmode);
        if($r === false)
            throw new MySQLiException($this->error, $this->errno);
        return $r;
    }
    #endregion

    #region Protected methods

    /**
     * Adapts field name or table names
     * @param string|string $name ex. adaptNames('string'|'\\string'|array)
     * @param bool $allow_arrays
     * @return string
     * @throws MySQLiAdaptingException
     */
    protected function escapeNames($name, $allow_arrays = true) {

        $esc = function($name) {
            if($name[0] == '`') return $name;
            if($name[0] == '\\') return substr($name, 1);
            return '`' . $this->escape_string($name) . '`';
        };

        if(!is_array($name))
            return $esc($name);
        if(!$allow_arrays)
            throw new MySQLiAdaptingException('According to your action, only strings will be allowed for $name');
        $map = array_map($esc, $name);
        return implode(',', $map);

    }

    #endregion
}
