<?php namespace Olive\Contracts\Validator\Rules;

use Exception;
use Rakit\Validation\Rule;

class JSONArray extends Rule
{
    protected $message = 'The :attribute is not a valid JSON Array';

    public function check($value): bool {
        try {
            if(is_array($value))
                return array_keys($value) === range(0, count($value) - 1);

            $json = json_decode($value, true);
            if ($json === null || !is_array($json)) return false;
            return array_keys($json) === range(0, count($json) - 1);
        } catch (Exception) {
            return false;
        }
    }
}