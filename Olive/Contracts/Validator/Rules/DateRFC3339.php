<?php


namespace Olive\Contracts\Validator\Rules;


use Rakit\Validation\MissingRequiredParameterException;
use Rakit\Validation\Rule;

class DateRFC3339 extends Rule
{
    /** @var string */
    protected $message = "The :attribute is not valid RFC3339 format";

    /** @var array */
    protected $fillableParams = ['format'];

    /** @var array */
    protected $params = [
        'format' => DATE_RFC3339,
    ];

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     * @throws MissingRequiredParameterException
     */
    public function check($value): bool {
        $this->requireParameters($this->fillableParams);

        $format = $this->parameter('format');
        return date_create_from_format($format, $value) !== false;
    }
}