<?php namespace Olive\Support\MySQLi;


use Olive\Exceptions\MySQLiAdaptingException;
use Olive\Exceptions\MySQLiConditionException;
use Olive\Exceptions\MySQLiException;
use Olive\Traits\Singleton;

class DB extends MySQLiConnection
{

    use Singleton;

    #region Query methods

    /**
     * @param string|string[] $table
     * @param string|array|Condition $condition
     * @param int[]|int $limit
     * @param string|string[] $columns
     * @param string $orderby
     * @param bool $execute <b>false</b>: return query only<br><b>true</b>: execute query and return the result
     * @return string|\mysqli_result
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function select($table, $condition = null, $limit = null, $columns = null, $orderby = null, $execute = true) {
        # table
        $table = $this->escapeNames($table);

        # where
        $where = $this->adaptCondition($condition);

        # limit
        $limit = $this->adaptLimit($limit);

        # columns
        $columns = $this->adaptColumns($columns);

        # orderby
        $orderby = $this->adaptOrderBy($orderby);

        # query
        /** @noinspection SqlNoDataSourceInspection */
        $query = "SELECT $columns FROM $table"
            . ($where ? " WHERE $where" : '')
            . ($orderby ? " ORDER BY $orderby" : "")
            . ($limit ? " LIMIT " . (is_array($limit) ? implode(',', $limit) : $limit) : '');
        # select
        return $execute ? $this->query($query) : $query;
    }

    /**
     * @param string $table
     * @param string|string[] $fields
     * @param array $arrayOfCorrespondingValues
     * @param bool $execute <b>false</b>: return query only<br><b>true</b>: execute query and return the result
     * @return string|\mysqli_result
     * @throws MySQLiAdaptingException
     * @throws MySQLiException
     */
    public function insert($table, $fields, $arrayOfCorrespondingValues, $execute = true) {
        # table
        $table = $this->escapeNames($table, false);

        # fields
        $fields = '(' . $this->escapeNames($fields) . ')';

        # values
        $str_vals = [];
        foreach ($arrayOfCorrespondingValues as &$correspondingValues) {
            $vals = [];
            foreach ($correspondingValues as &$value)
                $vals[] = $this->val($value);
            $str_vals[] = '(' . implode(',', $vals) . ')';
        }
        $str_vals = implode(',', $str_vals);

        # query
        /** @noinspection SqlNoDataSourceInspection */
        $query = "INSERT INTO $table $fields VALUES $str_vals";

        # insert
        return $execute ? $this->query($query) : $query;
    }

    /**
     * @param string $table
     * @param string[]|string $set associate array with field=>value or raw string
     * @param string|array|Condition $condition
     * @param bool $execute <b>false</b>: return query only<br><b>true</b>: execute query and return the result
     * @return \mysqli_result|string
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function update($table, $set, $condition = null, $execute = true) {
        # table
        $table = $this->escapeNames($table, false);

        #set
        if (is_array($set)) {
            $sets = [];
            foreach ($set as $field => $value) {
                if (!$field) throw new MySQLiAdaptingException('Field name is empty');
                $field  = $this->escapeNames($field, false);
                $value  = $this->val($value);
                $sets[] = "$field=$value";
            }
            $set = implode(',', $sets);
        }
        if (!is_string($set))
            throw new MySQLiAdaptingException('Invalid $set');

        # condition
        $condition = $this->adaptCondition($condition);

        # query
        /** @noinspection SqlNoDataSourceInspection */
        $query = "UPDATE $table SET $set" . ($condition ? " WHERE $condition" : '');

        # update
        return $execute ? $this->query($query) : $query;
    }

    /**
     * @param string $table
     * @param string|array|Condition $condition
     * @param bool $execute <b>false</b>: return query only<br><b>true</b>: execute query and return the result
     * @return \mysqli_result|string
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function delete($table, $condition, $execute = true) {
        # table
        $table = $this->escapeNames($table, false);

        # condition
        $condition = $this->adaptCondition($condition);

        # query
        /** @noinspection SqlNoDataSourceInspection */
        $query = "DELETE FROM $table" . ($condition ? " WHERE $condition" : '');

        # delete
        return $execute ? $this->query($query) : $query;
    }

    /**
     * @param string|string[] $table
     * @param string|array|Condition $condition
     * @param bool $execute
     * @return int|string
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function count($table, $condition = null, $execute = true) {
        # query
        $query = $this->select($table, $condition, null, '\\count(*) as __count', null, false);

        # update
        return $execute ? $this->fetchArray($this->query($query))[0]['__count'] : $query;

    }

    /**
     * @param $table
     * @param bool $execute
     * @return array[]|string Array of columns, keys in each column: <b>"Field"</b>, <b>"Type"</b>, <b>"Null"</b>, <b>"Default"</b>, <b>"Extra"</b>
     * @throws MySQLiAdaptingException
     * @throws MySQLiException
     */
    public function tableColumns($table, $execute = true) {
        # table
        $table = $this->escapeNames($table, false);

        # query
        $query = "SHOW COLUMNS FROM $table";

        # execute
        return $execute ? $this->fetchArray($this->query($query)) : $query;
    }

    /**
     * @param string $table
     * @param string $column
     * @param bool $execute
     * @return string[]|string Array of column's enum values
     * @throws MySQLiAdaptingException
     * @throws MySQLiException
     */
    public function columnEnums($table, $column, $execute = true) {
        # table
        $table = $this->escapeNames($table, false);

        # column
        $column = $this->val($column);

        # query
        $query = "SHOW COLUMNS FROM $table WHERE `Field`=$column";

        # check for execution
        if (!$execute)
            return $query;

        # execute SQL
        $result = $this->query($query);

        # parse
        $result = $this->fetchArray($result);
        if ($result == [])
            return [];
        preg_match("/^enum\(\'(.*)\'\)$/", $result[0]['Type'], $matches);
        if (count($matches) == 2)
            return explode("','", $matches[1]);

        return [];
    }

    /**
     * @param $table
     * @param string|array|Condition $condition
     * @return bool
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function exists($table, $condition = null) {
        # query
        $query = $this->select($table, $condition, null, null, null, false);

        # execute SQL
        return $this->query($query)->num_rows > 0;
    }

    #endregion

    #region Fetching methods
    /**
     * @param \mysqli_result $mysqli_result
     * @param string $class_name
     * @return Record[]|array
     */
    public function fetch(\mysqli_result $mysqli_result, $class_name = 'stdClass') {
        $out = [];
        while ($o = $mysqli_result->fetch_object($class_name))
            $out[] = $o instanceof Record ? $o->syncOriginal() : $o;
        return $out;
    }

    /**
     * @param \mysqli_result $mysqli_result
     * @param string $class_name
     * @return Record|\stdClass
     */
    public function fetchSingle(\mysqli_result $mysqli_result, $class_name = 'stdClass') {
        $r = $this->fetch($mysqli_result, $class_name);
        if ($r == []) return null;
        return $r[0];
    }

    public function fetchArray(\mysqli_result $mysqli_result, $associate = true) {
        $r         = [];
        $associate = $associate ? MYSQLI_ASSOC : MYSQLI_NUM;
        while ($f = $mysqli_result->fetch_array($associate))
            $r[] = $f;
        return $r;
    }

    #endregion

    #region Record methods
    /**
     * @param string $class_name Record's class name
     * @param array|string|Condition $condition
     * @param int[]|int $limit
     * @param string|string[] $columns
     * @param string $orderby
     * @return \stdClass|Record
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function selectRecord($class_name, $condition = null, $limit = null, $columns = null, $orderby = null) {
        $r = $this->selectRecords($class_name, $condition, $limit, $columns, $orderby);
        if ($r == []) return null;
        return $r[0];
    }

    /**
     * @param string $class_name Record's class name
     * @param array|string|Condition $condition
     * @param int[]|int $limit
     * @param string|string[] $columns
     * @param string $orderby
     * @return \stdClass[]|Record[]
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function selectRecords($class_name, $condition = null, $limit = null, $columns = null, $orderby = null) {
        /** @var Record $class_name */
        $r = $this->select($class_name::table(), $condition, $limit, $columns, $orderby);
        return $this->fetch($r, $class_name);
    }

    #endregion

    #region Helper methods
    /**
     * @param callable $run
     * @return bool|\Exception
     */
    public static function transaction(callable $run) {
        $db = static::getInstance();

        try {
            $db->begin_transaction();
            $run();
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollback();
            return $e;
        }
    }
    #endregion

    #region Adaptors

    /**
     * Adapt conditions
     * @param string|array|Condition $condition
     * @return null|string
     * @throws MySQLiConditionException
     */
    protected function adaptCondition($condition) {
        if (is_array($condition))
            return (new Condition($condition))->parse();
        elseif (is_string($condition))
            return $condition;
        elseif ($condition instanceof Condition)
            return $condition->parse();
        return null;
    }

    /**
     * Adapt columns
     * @param string|string $columns
     * @return string
     * @throws MySQLiAdaptingException
     */
    protected function adaptColumns($columns) {
        if (!$columns) return '*';
        return $this->escapeNames($columns);
    }

    /**
     * Adapt query limit
     * @param $limit
     * @return array|int|null
     * @throws MySQLiAdaptingException
     */
    protected function adaptLimit($limit) {
        if ($limit == null) return null;
        if (is_array($limit)) {
            if (count($limit) != 2)
                throw new MySQLiAdaptingException('Invalid limit array count.');
            return [intval($limit[0]), intval($limit[1])];
        }
        return intval($limit);
    }

    /**
     * Adapt orderby
     * @param $orderby
     * @return string
     */
    protected function adaptOrderBy($orderby) {
        return $this->escape_string($orderby);
    }

    #endregion

}