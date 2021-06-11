<?php namespace Olive\Contracts\Validator\Rules;

use Rakit\Validation\Rule;

class Exists extends Rule
{
    protected $message        = "The :attribute is not exists";
    protected $fillableParams = ['model', 'column'];

    public function check($value): bool {
        // make sure required parameters exists
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->requireParameters(['model', 'column']);

        // getting parameters
        $model  = $this->parameter('model');
        $column = $this->parameter('column');

        return $model::exists(
            [$column => $value]
        );
    }
}