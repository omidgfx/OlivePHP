<?php namespace Olive\Contracts\Validator\Rules;

use Olive\Util\Str;

class EndsWith extends StartsWith
{
    protected function doCheck($value, $search): bool {
        return Str::endsWith($search, $value, true);
    }
}