<?php namespace Olive\Contracts\Validator\Rules;

use Rakit\Validation\Rule;

class Uuid extends Rule
{

    public function check($value): bool {
        return preg_match("/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/", $value) === 1;
    }
}