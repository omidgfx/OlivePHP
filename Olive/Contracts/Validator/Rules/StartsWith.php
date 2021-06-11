<?php namespace Olive\Contracts\Validator\Rules;

use Olive\Util\Str;
use Rakit\Validation\Rule;

class StartsWith extends Rule
{
    protected $message = 'The :attribute only allows to starts with :search';

    /** @var bool */
    protected bool $strict = false;

    /**
     * Given $params and assign the $this->params
     *
     * @param array $params
     * @return self
     */
    public function fillParameters(array $params): Rule {
        if (count($params) === 1 && is_array($params[0])) {
            $params = $params[0];
        }
        $this->params['search'] = $params;
        return $this;
    }

    /**
     * Set strict value
     *
     * @param bool $strict
     * @return void
     */
    public function strict(bool $strict = true): void {
        $this->strict = $strict;
    }

    public function search(array $keywords): StartsWith {
        $this->params['search'] = $keywords;
        return $this;
    }

    public function check($value): bool {
        // make sure required parameters exists
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->requireParameters(['search']);

        // getting parameters
        $search = $this->parameter('search');

        return $this->doCheck($value, $search);
    }

    protected function doCheck($value, $search): bool {
        return Str::startsWith($search, $value, true);
    }
}