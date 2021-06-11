<?php


namespace Olive\Contracts\Validator\Rules;


use Rakit\Validation\Rule;

class Boolean extends Rule
{
    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value): bool {
        return is_bool($value) || in_array($value, ['true', 'yes', 'on', '1', 'false', 'no', 'off', '0'], false);
    }
}