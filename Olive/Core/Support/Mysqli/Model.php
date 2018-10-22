<?php namespace Olive\Support\MySQLi;

use Olive\Exceptions\MySQLiAdaptingException;
use Olive\Exceptions\MySQLiConditionException;
use Olive\Exceptions\MySQLiException;

/**
 * @property $id
 * Class Model
 * @package Olive\MySQLi
 */
abstract class Model extends Record {

    #region Writing methods

    public function delete() {
        # db
        $db = DB::getInstance();

        # delete
        $db->delete(static::table(), Condition::where('id', Condition::equal, $this->id));

        return $this;
    }

    /**
     * @param bool $syncOriginal syncOriginal after save successfully
     * @param bool $htmlSpecialCharsEncode Encode originals after save successfully and syncOriginal
     * @return Model
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    public function save($syncOriginal = TRUE, $htmlSpecialCharsEncode = TRUE) {
        if($this->id === NULL)
            return $this->insert($syncOriginal, $htmlSpecialCharsEncode);
        return $this->update($syncOriginal, $htmlSpecialCharsEncode);
    }

    /**
     * @param bool $syncOriginal syncOriginal after insert successfully
     * @param bool $htmlSpecialCharsEncode Encode originals after insert successfully and syncOriginal
     * @return $this
     * @throws MySQLiAdaptingException
     * @throws MySQLiException
     */
    protected function insert($syncOriginal = TRUE, $htmlSpecialCharsEncode = TRUE) {
        # changes
        $changes = $this->getChanges();

        # db
        $db = DB::getInstance();

        # insert
        $db->insert(static::table(), array_keys($changes), [array_values($changes)]);

        # apply changes
        $this->id = $db->insert_id;

        # normalize record
        if($syncOriginal) {
            if($htmlSpecialCharsEncode)
                $this->htmlSpecialCharsEncode(FALSE);
            $this->syncOriginal();
        }

        # make chain
        return $this;
    }

    /**
     * @param bool $syncOriginal syncOriginal after update successfully
     * @param bool $htmlSpecialCharsEncode Encode originals after update successfully and syncOriginal
     * @return $this
     * @throws MySQLiAdaptingException
     * @throws MySQLiConditionException
     * @throws MySQLiException
     */
    protected function update($syncOriginal = TRUE, $htmlSpecialCharsEncode = TRUE) {
        # changes
        $changes = $this->getChanges();
        if($changes == []) // nothing to save
            return $this;
        # db
        $db = DB::getInstance();

        # update
        $db->update(static::table(), $changes, Condition::where('id', $this->id));

        # normalize record
        if($syncOriginal) {
            if($htmlSpecialCharsEncode)
                $this->htmlSpecialCharsEncode(FALSE);
            $this->syncOriginal();
        }
        return $this;
    }

    #endregion
}