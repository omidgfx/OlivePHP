<?php namespace Olive\Contracts\Validator\Rules;

use Rakit\Validation\Rule;

class Unique extends Rule
{
    protected $message        = "The :attribute is exists";
    protected $fillableParams = ['model', 'column'];

    /** @noinspection PhpUnhandledExceptionInspection */
    public function check($value): bool {
        // make sure required parameters exists
        $this->requireParameters(['model', 'column']);

        // getting parameters
        $model  = $this->parameter('model');
        $column = $this->parameter('column');

        return !$model::exists(
            [$column => $value]
        );
    }
}