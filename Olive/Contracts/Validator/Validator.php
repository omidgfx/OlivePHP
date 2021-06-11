<?php namespace Olive\Contracts\Validator;

use Olive\Contracts\Validator\Rules\CallbackURL;
use Olive\Contracts\Validator\Rules\DateRFC3339;
use Olive\Contracts\Validator\Rules\EndsWith;
use Olive\Contracts\Validator\Rules\Exists;
use Olive\Contracts\Validator\Rules\JSONArray;
use Olive\Contracts\Validator\Rules\StartsWith;
use Olive\Contracts\Validator\Rules\Unique;
use Olive\Contracts\Validator\Rules\Uuid;

class Validator extends \Rakit\Validation\Validator
{
    /**
     * @throws \Rakit\Validation\RuleQuashException
     */
    public function __construct(array $messages = []) {
        parent::__construct($messages);
        $this->addValidator('exists', new Exists);
        $this->addValidator('unique', new Unique);
        $this->addValidator('starts_with', new StartsWith);
        $this->addValidator('ends_with', new EndsWith);
        $this->addValidator('uuid', new Uuid);
        $this->addValidator('json_array', new JSONArray);
        $this->addValidator('callback_uri', new CallbackURL);
        $this->addValidator('date_rfc3339', new DateRFC3339);
    }
}