<?php namespace Olive\MVC;

use Olive\Exceptions\MySQLiAdaptingException;
use Olive\Exceptions\MySQLiConditionException;
use Olive\Exceptions\MySQLiException;
use Olive\Support\MySQLi\Condition;
use Olive\Support\MySQLi\DB;
use Olive\Support\MySQLi\Record;
use Olive\Util\Text;

/**
 * @property $id
 * Class Model
 * @package Olive\MySQLi
 */
abstract class Model extends Record
{

    #region Helpers

    /**
     * @param string $pattern
     * @param string $column
     * @return string
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public static function uniqueRandomPattern($pattern, $column) {
        do {
            $random = Text::randomByPattern($pattern);
        } while (static::exists([$column => $random]));

        return $random;
    }

    #endregion

    #region Writing methods

    /**
     * @return $this
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function delete() {
        # db
        $db = DB::getInstance();

        # delete
        $db->delete(static::table(), Condition::where('id', Condition::equal, $this->id));

        return $this;
    }

    /**
     * @param bool $syncOriginal syncOriginal after save successfully
     * @return Model
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function save($syncOriginal = true) {
        if ($this->id === null)
            return $this->insert($syncOriginal);
        return $this->update($syncOriginal);
    }

    /**
     * @param bool $syncOriginal syncOriginal after insert successfully
     * @return $this
     * @throws MySQLiAdaptingException
     * @throws MySQLiException
     */
    protected function insert($syncOriginal = true) {
        # changes
        $changes = $this->getChanges();

        # db
        $db = DB::getInstance();

        # insert
        $db->insert(static::table(), array_keys($changes), [array_values($changes)]);

        # apply changes
        $this->id = $db->insert_id;

        # normalize record
        if ($syncOriginal)
            $this->syncOriginal();

        # make chain
        return $this;
    }

    /**
     * @param bool $syncOriginal syncOriginal after update successfully
     * @return $this
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    protected function update($syncOriginal = true) {
        # changes
        $changes = $this->getChanges();
        if (!$changes) // nothing to save
            return $this;
        # db
        $db = DB::getInstance();

        # update
        $db->update(static::table(), $changes, Condition::where('id', $this->id));

        # normalize record
        if ($syncOriginal)
            $this->syncOriginal();

        # make chain
        return $this;
    }

    #endregion

}
