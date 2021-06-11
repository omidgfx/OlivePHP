<?php namespace Olive\Contracts\Validator\Rules;

use Rakit\Validation\Rule;
use Rakit\Validation\Rules\Url;

class CallbackURL extends Rule
{
    protected $message = 'The :attribute is not valid url';

    public function check($value): bool {
        $n     = new Url;
        $value = strtolower($value);
        return
            $n->validateCommonScheme($value, 'http') ||
            $n->validateCommonScheme($value, 'https');
    }
}