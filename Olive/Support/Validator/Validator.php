<?php /** @noinspection All */

namespace Olive\Support\Validator;

use DateTime;
use Olive\Exceptions\ValidatorException;

/**
 * GUMP - A fast, extensible PHP input validation class.
 *
 * @author      Sean Nieuwoudt (http://twitter.com/SeanNieuwoudt)
 * @author      Filis Futsarov (http://twitter.com/FilisCode)
 * @copyright   Copyright (c) 2017 wixelhq.com
 *
 * @version     1.5
 */
class Validator
{
    // Singleton instance of GUMP
    protected static $instance = null;

    // Validation rules for execution
    protected $validationRules = [];

    // Filter rules for execution
    protected $filterRules = [];

    // Instance attribute containing errors from last run
    protected $errors = [];

    // Contain readable field names that have been set manually
    protected static $fields = [];

    // Custom validation methods
    protected static $validationMethods = [];

    // Custom validation methods error messages and custom ones
    protected static $validationMethodsErrors = [];

    // Customer filter methods
    protected static $filterMethods = [];


    // ** ------------------------- Instance Helper ---------------------------- ** //

    /**
     * Function to create and return previously created instance
     *
     * @return Validator
     * @throws ValidatorException
     */

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new static;
        }
        return self::$instance;
    }


    // ** ------------------------- Validation Data ------------------------------- ** //

    public static $basic_tags = '<br><p><a><strong><b><i><em><img><blockquote><code><dd><dl><hr><h1><h2><h3><h4><h5><h6><label><ul><li><span><sub><sup>';

    public static $en_noise_words = "about,after,all,also,an,and,another,any,are,as,at,be,because,been,before,
                                     being,between,both,but,by,came,can,come,could,did,do,each,for,from,get,
                                     got,has,had,he,have,her,here,him,himself,his,how,if,in,into,is,it,its,it's,like,
                                     make,many,me,might,more,most,much,must,my,never,now,of,on,only,or,other,
                                     our,out,over,said,same,see,should,since,some,still,such,take,than,that,
                                     the,their,them,then,there,these,they,this,those,through,to,too,under,up,
                                     very,was,way,we,well,were,what,where,which,while,who,with,would,you,your,a,
                                     b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,$,1,2,3,4,5,6,7,8,9,0,_";

    // field characters below will be replaced with a space.
    protected $fieldCharsToRemove = ['_', '-'];

    protected $lang;


    // ** ------------------------- Validation Helpers ---------------------------- ** //

    public function __construct($lang = 'en') {
        if ($lang) {
            $lang_file = __DIR__ . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $lang . '.php';

            if (file_exists($lang_file)) {
                $this->lang = $lang;
            } else {
                throw new ValidatorException('Language with key "' . $lang . '" does not exist');
            }
        }
    }

    /**
     * Shorthand method for inline validation.
     *
     * @param array $data The data to be validated
     * @param array $validators The GUMP validators
     *
     * @return mixed True(boolean) or the array of error messages
     * @throws ValidatorException
     */
    public static function isValid(array $data, array $validators) {
        $gump = self::getInstance();

        $gump->validationRules($validators);

        if ($gump->run($data) === false) {
            return $gump->getReadableErrors(false);
        } else {
            return true;
        }
    }

    /**
     * Shorthand method for running only the data filters.
     *
     * @param array $data
     * @param array $filters
     *
     * @return mixed
     * @throws ValidatorException
     */
    public static function filterInput(array $data, array $filters) {
        $gump = self::getInstance();

        return $gump->filter($data, $filters);
    }

    /**
     * Magic method to generate the validation error messages.
     *
     * @return string
     * @throws ValidatorException
     */
    public function __toString() {
        return $this->getReadableErrors(true);
    }

    /**
     * Perform XSS clean to prevent cross site scripting.
     *
     * @static
     *
     * @param array $data
     *
     * @return array
     */
    public static function xssClean(array $data) {
        foreach ($data as $k => $v) {
            $data[$k] = filter_var($v, FILTER_SANITIZE_STRING);
        }

        return $data;
    }

    /**
     * Adds a custom validation rule using a callback function.
     *
     * @param string $rule
     * @param callable $callback
     * @param string $error_message
     *
     * @return bool
     *
     * @throws ValidatorException
     */
    public static function addValidator($rule, $callback, $error_message = null) {
        $method = 'validate_' . $rule;

        if (method_exists(__CLASS__, $method) || isset(self::$validationMethods[$rule])) {
            throw new ValidatorException("Validator rule '$rule' already exists.");
        }

        self::$validationMethods[$rule] = $callback;
        if ($error_message) {
            self::$validationMethodsErrors[$rule] = $error_message;
        }

        return true;
    }

    /**
     * Adds a custom filter using a callback function.
     *
     * @param string $rule
     * @param callable $callback
     *
     * @return bool
     *
     * @throws ValidatorException
     */
    public static function addFilter($rule, $callback) {
        $method = 'filter_' . $rule;

        if (method_exists(__CLASS__, $method) || isset(self::$filterMethods[$rule])) {
            throw new ValidatorException("Filter rule '$rule' already exists.");
        }

        self::$filterMethods[$rule] = $callback;

        return true;
    }

    /**
     * Helper method to extract an element from an array safely
     *
     * @param mixed $key
     * @param array $array
     * @param mixed $default
     * @return mixed
     */
    public static function field($key, array $array, $default = null) {
        if (!is_array($array)) {
            return null;
        }

        if (isset($array[$key])) {
            return $array[$key];
        } else {
            return $default;
        }
    }

    /**
     * Getter/Setter for the validation rules.
     *
     * @param array $rules
     *
     * @return array
     */
    public function validationRules(array $rules = []) {
        if (empty($rules)) {
            return $this->validationRules;
        }

        return $this->validationRules = $rules;
    }

    /**
     * Getter/Setter for the filter rules.
     *
     * @param array $rules
     *
     * @return array
     */
    public function filterRules(array $rules = []) {
        if (empty($rules)) {
            return $this->filterRules;
        }

        return $this->filterRules = $rules;
    }

    /**
     * Run the filtering and validation after each other.
     *
     * @param array $data
     * @param bool $check_fields
     *
     * @return array|false
     *
     * @throws ValidatorException
     */
    public function run(array $data, $check_fields = false) {
        $data = $this->filter($data, $this->filterRules());

        $validated = $this->validate(
            $data, $this->validationRules()
        );

        if ($check_fields === true) {
            $this->checkFields($data);
        }

        if ($validated !== true) {
            return false;
        }

        return $data;
    }

    /**
     * Ensure that the field counts match the validation rule counts.
     *
     * @param array $data
     */
    private function checkFields(array $data) {
        $ruleset  = $this->validationRules();
        $mismatch = array_diff_key($data, $ruleset);
        $fields   = array_keys($mismatch);

        foreach ($fields as $field) {
            $this->errors[] = [
                'field' => $field,
                'value' => $data[$field],
                'rule'  => 'mismatch',
                'param' => null,
            ];
        }
    }

    /**
     * Sanitize the input data.
     *
     * @param array $input
     * @param array $fields
     * @param bool $utf8_encode
     *
     * @return array
     */
    public function sanitize(array $input, array $fields = [], $utf8_encode = true) {
        $magic_quotes = (bool)get_magic_quotes_gpc();

        if (empty($fields)) {
            $fields = array_keys($input);
        }

        $return = [];

        foreach ($fields as $field) {
            if (!isset($input[$field])) {
                continue;
            } else {
                $value = $input[$field];
                if (is_array($value)) {
                    $value = $this->sanitize($value);
                }
                if (is_string($value)) {
                    if ($magic_quotes === true) {
                        $value = stripslashes($value);
                    }

                    if (strpos($value, "\r") !== false) {
                        $value = trim($value);
                    }

                    if (function_exists('iconv') && function_exists('mb_detect_encoding') && $utf8_encode) {
                        $current_encoding = mb_detect_encoding($value);

                        if ($current_encoding != 'UTF-8' && $current_encoding != 'UTF-16') {
                            $value = iconv($current_encoding, 'UTF-8', $value);
                        }
                    }

                    $value = filter_var($value, FILTER_SANITIZE_STRING);
                }

                $return[$field] = $value;
            }
        }

        return $return;
    }

    /**
     * Return the error array from the last validation run.
     *
     * @return array
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Perform data validation against the provided ruleset.
     *
     * @param mixed $input
     * @param array $ruleset
     *
     * @return mixed
     *
     * @throws ValidatorException
     */
    public function validate(array $input, array $ruleset) {
        $this->errors = [];

        foreach ($ruleset as $field => $rules) {

            $rules = explode('|', $rules);

            $look_for = ['required_file', 'required'];

            if (count(array_intersect($look_for, $rules)) > 0 || (isset($input[$field]))) {

                if (isset($input[$field])) {
                    if (is_array($input[$field]) && in_array('required_file', $ruleset)) {
                        $input_array = $input[$field];
                    } else {
                        $input_array = [$input[$field]];
                    }
                } else {
                    $input_array = [''];
                }

                foreach ($input_array as $value) {

                    $input[$field] = $value;

                    foreach ($rules as $rule) {
                        $method = null;
                        $param  = null;

                        // Check if we have rule parameters
                        if (strstr($rule, ',') !== false) {
                            $rule   = explode(',', $rule);
                            $method = 'validate_' . $rule[0];
                            $param  = $rule[1];
                            $rule   = $rule[0];

                            // If there is a reference to a field
                            if (preg_match('/(?:(?:^|;)_([a-z_]+))/', $param, $matches)) {

                                // If provided parameter is a field
                                if (isset($input[$matches[1]])) {
                                    $param = str_replace('_' . $matches[1], $input[$matches[1]], $param);
                                }
                            }
                        } else {
                            $method = 'validate_' . $rule;
                        }

                        //self::$validation_methods[$rule] = $callback;

                        if (is_callable([$this, $method])) {
                            $result = $this->$method(
                                $field, $input, $param
                            );

                            if (is_array($result)) {
                                if (array_search($result['field'], array_column($this->errors, 'field')) === false) {
                                    $this->errors[] = $result;
                                }
                            }

                        } elseif (isset(self::$validationMethods[$rule])) {
                            $result = call_user_func(self::$validationMethods[$rule], $field, $input, $param);

                            if ($result === false) {
                                if (array_search($result['field'], array_column($this->errors, 'field')) === false) {
                                    $this->errors[] = [
                                        'field' => $field,
                                        'value' => $input[$field],
                                        'rule'  => $rule,
                                        'param' => $param,
                                    ];
                                }
                            }

                        } else {
                            throw new ValidatorException("Validator method '$method' does not exist.");
                        }
                    }
                }
            }
        }

        return (count($this->errors) > 0) ? $this->errors : true;
    }

    /**
     * Set a readable name for a specified field names.
     *
     * @param string $field
     * @param string $readable_name
     */
    public static function setFieldName($field, $readable_name) {
        self::$fields[$field] = $readable_name;
    }

    /**
     * Set readable name for specified fields in an array.
     *
     * Usage:
     *
     * GUMP::set_field_names(array(
     *  "name" => "My Lovely Name",
     *  "username" => "My Beloved Username",
     * ));
     *
     * @param array $array
     */
    public static function setFieldNames(array $array) {
        foreach ($array as $field => $readable_name) {
            self::setFieldName($field, $readable_name);
        }
    }

    /**
     * Set a custom error message for a validation rule.
     *
     * @param string $rule
     * @param string $message
     * @throws ValidatorException
     */
    public static function setErrorMessage($rule, $message) {
        self::getInstance();
        self::$validationMethodsErrors[$rule] = $message;
    }

    /**
     * Set custom error messages for validation rules in an array.
     *
     * Usage:
     *
     * GUMP::set_error_messages(array(
     *  "validate_required"     => "{field} is required",
     *  "validate_valid_email"  => "{field} must be a valid email",
     * ));
     *
     * @param array $array
     * @throws ValidatorException
     */
    public static function setErrorMessages(array $array) {
        foreach ($array as $rule => $message) {
            self::setErrorMessage($rule, $message);
        }
    }

    /**
     * Get error messages.
     *
     * @return array
     */
    protected function getMessages() {
        $lang_file = __DIR__ . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $this->lang . '.php';
        /** @noinspection PhpIncludeInspection */
        $messages = require $lang_file;

        if ($validation_methods_errors = self::$validationMethodsErrors) {
            $messages = array_merge($messages, $validation_methods_errors);
        }
        return $messages;
    }

    /**
     * Process the validation errors and return human readable error messages.
     *
     * @param bool $convert_to_string = false
     * @param string $field_class
     * @param string $error_class
     *
     * @return array|string
     * @throws ValidatorException
     * @throws ValidatorException
     */
    public function getReadableErrors($convert_to_string = false, $field_class = 'gump-field', $error_class = 'gump-error-message') {
        if (empty($this->errors)) {
            return ($convert_to_string) ? null : [];
        }

        $resp = [];

        // Error messages
        $messages = $this->getMessages();

        foreach ($this->errors as $e) {
            $field = ucwords(str_replace($this->fieldCharsToRemove, chr(32), $e['field']));
            $param = $e['param'];

            // Let's fetch explicitly if the field names exist
            if (array_key_exists($e['field'], self::$fields)) {
                $field = self::$fields[$e['field']];

                // If param is a field (i.e. equalsfield validator)
                if (array_key_exists($param, self::$fields)) {
                    $param = self::$fields[$e['param']];
                }
            }

            // Messages
            if (isset($messages[$e['rule']])) {
                if (is_array($param)) {
                    $param = implode(', ', $param);
                }
                $message = str_replace('{param}', $param, str_replace('{field}', '<span class="' . $field_class . '">' . $field . '</span>', $messages[$e['rule']]));
                $resp[]  = $message;
            } else {
                throw new ValidatorException ('Rule "' . $e['rule'] . '" does not have an error message');
            }
        }

        if (!$convert_to_string) {
            return $resp;
        } else {
            $buffer = '';
            foreach ($resp as $s) {
                $buffer .= "<span class=\"$error_class\">$s</span>";
            }
            return $buffer;
        }
    }

    /**
     * Process the validation errors and return an array of errors with field names as keys.
     *
     * @param bool $humanizeFieldName
     * @return array
     * @throws ValidatorException
     */
    public function getErrorsArray($humanizeFieldName = true) {

        $resp = [];

        if (empty($this->errors))
            return $resp;

        // Error messages
        $messages = $this->getMessages();

        foreach ($this->errors as $e) {
            $field = $humanizeFieldName ? ucwords(str_replace(['_', '-'], chr(32), $e['field'])) : $e['field'];
            $param = $e['param'];

            // Let's fetch explicitly if the field names exist
            if (array_key_exists($e['field'], self::$fields)) {
                $field = self::$fields[$e['field']];

                // If param is a field (i.e. equalsfield validator)
                if (array_key_exists($param, self::$fields)) {
                    $param = self::$fields[$e['param']];
                }
            }

            // Messages
            if (isset($messages[$e['rule']])) {
                // Show first validation error and don't allow to be overwritten
                if (!isset($resp[$e['field']])) {
                    if (is_array($param)) {
                        $param = implode(', ', $param);
                    }
                    $message           = str_replace('{param}', $param, str_replace('{field}', $field, $messages[$e['rule']]));
                    $resp[$e['field']] = $message;
                }
            } else {
                throw new ValidatorException ('Rule "' . $e['rule'] . '" does not have an error message');
            }
        }

        return $resp;
    }

    /**
     * Filter the input data according to the specified filter set.
     *
     * @param mixed $input
     * @param array $filterset
     *
     * @return mixed
     *
     * @throws ValidatorException
     *
     * @throws ValidatorException
     */
    public function filter(array $input, array $filterset) {
        foreach ($filterset as $field => $filters) {
            if (!array_key_exists($field, $input)) {
                continue;
            }

            $filters = explode('|', $filters);

            foreach ($filters as $filter) {
                $params = null;

                if (strstr($filter, ',') !== false) {
                    $filter = explode(',', $filter);

                    $params = array_slice($filter, 1, count($filter) - 1);

                    $filter = $filter[0];
                }

                if (is_array($input[$field])) {
                    $input_array = &$input[$field];
                } else {
                    $input_array = [&$input[$field]];
                }

                foreach ($input_array as &$value) {
                    if (is_callable([$this, 'filter_' . $filter])) {
                        $method = 'filter_' . $filter;
                        $value  = $this->$method($value, $params);
                    } elseif (function_exists($filter)) {
                        $value = $filter($value);
                    } elseif (isset(self::$filterMethods[$filter])) {
                        $value = call_user_func(self::$filterMethods[$filter], $value, $params);
                    } else {
                        throw new ValidatorException("Filter method '$filter' does not exist.");
                    }
                }
            }
        }

        return $input;
    }

    // ** ------------------------- Filters --------------------------------------- ** //

    /**
     * Replace noise words in a string (http://tax.cchgroup.com/help/Avoiding_noise_words_in_your_search.htm).
     *
     * Usage: '<index>' => 'noise_words'
     *
     * @param string $value
     *
     * @return string
     */
    protected function filter_noise_words($value) {
        $value = preg_replace('/\s\s+/u', chr(32), $value);

        $value = " $value ";

        $words = explode(',', self::$en_noise_words);

        foreach ($words as $word) {
            $word = trim($word);

            $word = " $word "; // Normalize

            if (stripos($value, $word) !== false) {
                $value = str_ireplace($word, chr(32), $value);
            }
        }

        return trim($value);
    }

    /**
     * Remove all known punctuation from a string.
     *
     * Usage: '<index>' => 'rmpunctuataion'
     *
     * @param string $value
     *
     * @return string
     */
    protected function filter_rmpunctuation($value) {
        return preg_replace("/(?![.=$'€%-])\p{P}/u", '', $value);
    }

    /**
     * Sanitize the string by removing any script tags.
     *
     * Usage: '<index>' => 'sanitize_string'
     *
     * @param string $value
     *
     * @return string
     */
    protected function filter_sanitize_string($value) {
        return filter_var($value, FILTER_SANITIZE_STRING);
    }

    /**
     * Sanitize the string by urlencoding characters.
     *
     * Usage: '<index>' => 'urlencode'
     *
     * @param string $value
     *
     * @return string
     */
    protected function filter_urlencode($value) {
        return filter_var($value, FILTER_SANITIZE_ENCODED);
    }

    /**
     * Sanitize the string by converting HTML characters to their HTML entities.
     *
     * Usage: '<index>' => 'htmlencode'
     *
     * @param string $value
     *
     * @return string
     */
    protected function filter_htmlencode($value) {
        return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
    }

    /**
     * Sanitize the string by removing illegal characters from emails.
     *
     * Usage: '<index>' => 'sanitize_email'
     *
     * @param string $value
     * @return string
     */
    protected function filter_sanitize_email($value) {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize the string by removing illegal characters from numbers.
     *
     * @param string $value
     * @return string
     */
    protected function filter_sanitize_numbers($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize the string by removing illegal characters from float numbers.
     *
     * @param string $value
     * @return string
     */
    protected function filter_sanitize_floats($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Filter out all HTML tags except the defined basic tags.
     *
     * @param string $value
     * @return string
     */
    protected function filter_basic_tags($value) {
        return strip_tags($value, self::$basic_tags);
    }

    /**
     * Convert the provided numeric value to a whole number.
     *
     * @param string $value
     * @return string
     */
    protected function filter_whole_number($value) {
        return intval($value);
    }

    /**
     * Convert MS Word special characters to web safe characters.
     * [“, ”, ‘, ’, –, …] => [", ", ', ', -, ...]
     *
     * @param string $value
     * @return string
     */
    protected function filter_ms_word_characters($value) {
        $word_open_double  = '“';
        $word_close_double = '”';
        $web_safe_double   = '"';

        $value = str_replace([$word_open_double, $word_close_double], $web_safe_double, $value);

        $word_open_single  = '‘';
        $word_close_single = '’';
        $web_safe_single   = "'";

        $value = str_replace([$word_open_single, $word_close_single], $web_safe_single, $value);

        $word_em     = '–';
        $web_safe_em = '-';

        $value = str_replace($word_em, $web_safe_em, $value);

        $word_ellipsis = '…';
        $web_ellipsis  = '...';

        $value = str_replace($word_ellipsis, $web_ellipsis, $value);

        return $value;
    }

    /**
     * Converts to lowercase.
     *
     * @param string $value
     * @return string
     */
    protected function filter_lower_case($value) {
        return strtolower($value);
    }

    /**
     * Converts to uppercase.
     *
     * @param string $value
     * @return string
     */
    protected function filter_upper_case($value) {
        return strtoupper($value);
    }

    /**
     * Converts value to url-web-slugs.
     *
     * Credit:
     * https://stackoverflow.com/questions/40641973/php-to-convert-string-to-slug
     * http://cubiq.org/the-perfect-php-clean-url-generator
     *
     * @param $str
     * @param string $delimiter
     * @return string
     */
    protected function filter_slug($str, $delimiter = '-') {
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;
    }

    // ** ------------------------- Validators ------------------------------------ ** //


    /**
     * Verify that a value is contained within the pre-defined value set.
     *
     * Usage: '<index>' => 'contains,value value value'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_contains($field, $input, $param = null) {
        if (!isset($input[$field])) {
            return null;
        }

        $param = trim(strtolower($param));

        $value = trim(strtolower($input[$field]));

        if (preg_match_all('#\'(.+?)\'#', $param, $matches, PREG_PATTERN_ORDER)) {
            $param = $matches[1];
        } else {
            $param = explode(chr(32), $param);
        }

        if (in_array($value, $param)) { // valid, return nothing
            return null;
        }

        return [
            'field' => $field,
            'value' => $value,
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Verify that a value is contained within the pre-defined value set.
     * OUTPUT: will NOT show the list of values.
     *
     * Usage: '<index>' => 'contains_list,value;value;value'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_contains_list($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        $param = trim(strtolower($param));

        $value = trim(strtolower($input[$field]));

        $param = explode(';', $param);

        // consider: in_array(strtolower($value), array_map('strtolower', $param)

        if (in_array($value, $param)) { // valid, return nothing
            return null;
        } else {
            return [
                'field' => $field,
                'value' => $value,
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
    }

    /**
     * Verify that a value is NOT contained within the pre-defined value set.
     * OUTPUT: will NOT show the list of values.
     *
     * Usage: '<index>' => 'doesnt_contain_list,value;value;value'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_doesnt_contain_list($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        $param = trim(strtolower($param));

        $value = trim(strtolower($input[$field]));

        $param = explode(';', $param);

        if (!in_array($value, $param)) { // valid, return nothing
            return null;
        } else {
            return [
                'field' => $field,
                'value' => $value,
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
    }

    /**
     * Check if the specified key is present and not empty.
     *
     * Usage: '<index>' => 'required'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_required($field, $input, $param = null) {
        if (isset($input[$field]) && ($input[$field] === false || $input[$field] === 0 || $input[$field] === 0.0 || $input[$field] === '0' || !empty($input[$field]))) {
            return null;
        }

        return [
            'field' => $field,
            'value' => null,
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Determine if the provided email is valid.
     *
     * Usage: '<index>' => 'valid_email'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_valid_email($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_EMAIL)) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value length is less or equal to a specific value.
     *
     * Usage: '<index>' => 'max_len,240'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_max_len($field, $input, $param = null) {
        if (!isset($input[$field])) {
            return null;
        }

        if (function_exists('mb_strlen')) {
            if (mb_strlen($input[$field]) <= (int)$param) {
                return null;
            }
        } else {
            if (strlen($input[$field]) <= (int)$param) {
                return null;
            }
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Determine if the provided value length is more or equal to a specific value.
     *
     * Usage: '<index>' => 'min_len,4'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_min_len($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (function_exists('mb_strlen')) {
            if (mb_strlen($input[$field]) >= (int)$param) {
                return null;
            }
        } else {
            if (strlen($input[$field]) >= (int)$param) {
                return null;
            }
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Determine if the provided value length matches a specific value.
     *
     * Usage: '<index>' => 'exact_len,5'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_exact_len($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (function_exists('mb_strlen')) {
            if (mb_strlen($input[$field]) == (int)$param) {
                return null;
            }
        } else {
            if (strlen($input[$field]) == (int)$param) {
                return null;
            }
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Determine if the provided value contains only alpha characters.
     *
     * Usage: '<index>' => 'alpha'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_alpha($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match('/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])+$/i', $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value contains only alpha-numeric characters.
     *
     * Usage: '<index>' => 'alpha_numeric'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_alpha_numeric($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match('/^([a-z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])+$/i', $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value contains only alpha characters with dashed and underscores.
     *
     * Usage: '<index>' => 'alpha_dash'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_alpha_dash($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match('/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ_-])+$/i', $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value contains only alpha numeric characters with spaces.
     *
     * Usage: '<index>' => 'alpha_numeric_space'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_alpha_numeric_space($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match("/^([a-z0-9ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s])+$/i", $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value contains only alpha numeric characters with spaces.
     *
     * Usage: '<index>' => 'alpha_space'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_alpha_space($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match("/^([0-9a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\s])+$/i", $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value is a valid number or numeric string.
     *
     * Usage: '<index>' => 'numeric'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_numeric($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!is_numeric($input[$field])) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value is a valid integer.
     *
     * Usage: '<index>' => 'integer'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_integer($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (filter_var($input[$field], FILTER_VALIDATE_INT) === false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value is a PHP accepted boolean.
     *
     * Usage: '<index>' => 'boolean'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_boolean($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field]) && $input[$field] !== 0) {
            return null;
        }

        $booleans = ['1', 'true', true, 1, '0', 'false', false, 0, 'yes', 'no', 'on', 'off'];
        if (in_array($input[$field], $booleans, true)) {
            return null;
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Determine if the provided value is a valid float.
     *
     * Usage: '<index>' => 'float'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_float($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (filter_var($input[$field], FILTER_VALIDATE_FLOAT) === false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value is a valid URL.
     *
     * Usage: '<index>' => 'valid_url'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_valid_url($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_URL)) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if a URL exists & is accessible.
     *
     * Usage: '<index>' => 'url_exists'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_url_exists($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        $url = parse_url(strtolower($input[$field]));

        if (isset($url['host'])) {
            $url = $url['host'];
        }

        if (function_exists('checkdnsrr') && function_exists('idn_to_ascii')) {
            if (checkdnsrr(idn_to_ascii($url), 'A') === false) {
                return [
                    'field' => $field,
                    'value' => $input[$field],
                    'rule'  => __FUNCTION__,
                    'param' => $param,
                ];
            }
        } else {
            if (gethostbyname($url) == $url) {
                return [
                    'field' => $field,
                    'value' => $input[$field],
                    'rule'  => __FUNCTION__,
                    'param' => $param,
                ];
            }
        }
        return null;
    }

    /**
     * Determine if the provided value is a valid IP address.
     *
     * Usage: '<index>' => 'valid_ip'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_ip($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_IP) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value is a valid IPv4 address.
     *
     * Usage: '<index>' => 'valid_ipv4'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     *
     * @see http://pastebin.com/UvUPPYK0
     */

    /*
     * What about private networks? http://en.wikipedia.org/wiki/Private_network
     * What about loop-back address? 127.0.0.1
     */
    protected function validate_valid_ipv4($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // removed !== FALSE

            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value is a valid IPv6 address.
     *
     * Usage: '<index>' => 'valid_ipv6'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_ipv6($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!filter_var($input[$field], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the input is a valid credit card number.
     *
     * See: http://stackoverflow.com/questions/174730/what-is-the-best-way-to-validate-a-credit-card-in-php
     * Usage: '<index>' => 'valid_cc'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_cc($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        $number = preg_replace('/\D/', '', $input[$field]);

        if (function_exists('mb_strlen')) {
            $number_length = mb_strlen($number);
        } else {
            $number_length = strlen($number);
        }


        /**
         * Bail out if $number_length is 0.
         * This can be the case if a user has entered only alphabets
         *
         * @since 1.5
         */
        if ($number_length == 0) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }


        $parity = $number_length % 2;

        $total = 0;

        for ($i = 0; $i < $number_length; ++$i) {
            $digit = $number[$i];

            if ($i % 2 == $parity) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $total += $digit;
        }

        if ($total % 10 == 0) {
            return null; // Valid
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Determine if the input is a valid human name [Credits to http://github.com/ben-s].
     *
     * See: https://github.com/Wixel/GUMP/issues/5
     * Usage: '<index>' => 'valid_name'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_name($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match("/^([a-z \p{L} '-])+$/i", $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided input is likely to be a street address using weak detection.
     *
     * Usage: '<index>' => 'street_address'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_street_address($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        // Theory: 1 number, 1 or more spaces, 1 or more words
        $hasLetter = preg_match('/[a-zA-Z]/', $input[$field]);
        $hasDigit  = preg_match('/\d/', $input[$field]);
        $hasSpace  = preg_match('/\s/', $input[$field]);

        $passes = $hasLetter && $hasDigit && $hasSpace;

        if (!$passes) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value is a valid IBAN.
     *
     * Usage: '<index>' => 'iban'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_iban($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        static $character = [
            'A' => 10,
            'C' => 12,
            'D' => 13,
            'E' => 14,
            'F' => 15,
            'G' => 16,
            'H' => 17,
            'I' => 18,
            'J' => 19,
            'K' => 20,
            'L' => 21,
            'M' => 22,
            'N' => 23,
            'O' => 24,
            'P' => 25,
            'Q' => 26,
            'R' => 27,
            'S' => 28,
            'T' => 29,
            'U' => 30,
            'V' => 31,
            'W' => 32,
            'X' => 33,
            'Y' => 34,
            'Z' => 35,
            'B' => 11,
        ];

        if (!preg_match("/\A[A-Z]{2}\d{2} ?[A-Z\d]{4}( ?\d{4}){1,} ?\d{1,4}\z/", $input[$field])) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }

        $iban = str_replace(' ', '', $input[$field]);
        $iban = substr($iban, 4) . substr($iban, 0, 4);
        $iban = strtr($iban, $character);

        if (bcmod($iban, 97) != 1) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided input is a valid date (ISO 8601)
     * or specify a custom format.
     *
     * Usage: '<index>' => 'date'
     *
     * @param string $field
     * @param string|array $input date ('Y-m-d') or datetime ('Y-m-d H:i:s')
     * @param string $param Custom date format
     *
     * @return mixed
     */
    protected function validate_date($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        // Default
        if (!$param) {
            $cdate1 = date('Y-m-d', strtotime($input[$field]));
            $cdate2 = date('Y-m-d H:i:s', strtotime($input[$field]));

            if ($cdate1 != $input[$field] && $cdate2 != $input[$field]) {
                return [
                    'field' => $field,
                    'value' => $input[$field],
                    'rule'  => __FUNCTION__,
                    'param' => $param,
                ];
            }
        } else {
            $date = DateTime::createFromFormat($param, $input[$field]);

            if ($date === false || $input[$field] != date($param, $date->getTimestamp())) {
                return [
                    'field' => $field,
                    'value' => $input[$field],
                    'rule'  => __FUNCTION__,
                    'param' => $param,
                ];
            }
        }
        return null;
    }

    /**
     * Determine if the provided input meets age requirement (ISO 8601).
     *
     * Usage: '<index>' => 'min_age,13'
     *
     * @param string $field
     * @param string|array $input date ('Y-m-d') or datetime ('Y-m-d H:i:s')
     * @param string $param int
     *
     * @return mixed
     * @throws \Exception
     */
    protected function validate_min_age($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        $cdate1 = new DateTime(date('Y-m-d', strtotime($input[$field])));
        $today  = new DateTime(date('d-m-Y'));

        $interval = $cdate1->diff($today);
        $age      = $interval->y;

        if ($age <= $param) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided numeric value is lower or equal to a specific value.
     *
     * Usage: '<index>' => 'max_numeric,50'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     *
     * @return mixed
     */
    protected function validate_max_numeric($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (is_numeric($input[$field]) && is_numeric($param) && ($input[$field] <= $param)) {
            return null;
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Determine if the provided numeric value is higher or equal to a specific value.
     *
     * Usage: '<index>' => 'min_numeric,1'
     *
     * @param string $field
     * @param array $input
     * @param null $param
     * @return mixed
     */
    protected function validate_min_numeric($field, $input, $param = null) {
        if (!isset($input[$field]) || $input[$field] === '') {
            return null;
        }

        if (is_numeric($input[$field]) && is_numeric($param) && ($input[$field] >= $param)) {
            return null;
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Determine if the provided value starts with param.
     *
     * Usage: '<index>' => 'starts,Z'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_starts($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (strpos($input[$field], $param) !== 0) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Checks if a file was uploaded.
     *
     * Usage: '<index>' => 'required_file'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_required_file($field, $input, $param = null) {
        if (!isset($input[$field])) {
            return null;
        }

        if (is_array($input[$field]) && $input[$field]['error'] !== 4) {
            return null;
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Check the uploaded file for extension for now
     * checks only the ext should add mime type check.
     *
     * Usage: '<index>' => 'extension,png;jpg;gif
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_extension($field, $input, $param = null) {
        if (!isset($input[$field])) {
            return null;
        }

        if (is_array($input[$field]) && $input[$field]['error'] !== 4) {
            $param              = trim(strtolower($param));
            $allowed_extensions = explode(';', $param);

            $path_info = pathinfo($input[$field]['name']);
            $extension = isset($path_info['extension']) ? $path_info['extension'] : false;

            if ($extension && in_array(strtolower($extension), $allowed_extensions)) {
                return null;
            }

            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided field value equals current field value.
     *
     *
     * Usage: '<index>' => 'equalsfield,Z'
     *
     * @param string $field
     * @param string|array $input
     * @param string $param field to compare with
     *
     * @return mixed
     */
    protected function validate_equalsfield($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if ($input[$field] == $input[$param]) {
            return null;
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Determine if the provided field value is a valid GUID (v4)
     *
     * Usage: '<index>' => 'guidv4'
     *
     * @param string $field
     * @param string|array $input
     * @param string $param field to compare with
     * @return mixed
     */
    protected function validate_guidv4($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (preg_match("/\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/", $input[$field])) {
            return null;
        }

        return [
            'field' => $field,
            'value' => $input[$field],
            'rule'  => __FUNCTION__,
            'param' => $param,
        ];
    }

    /**
     * Trims whitespace only when the value is a scalar.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function trimScalar($value) {
        if (is_scalar($value)) {
            $value = trim($value);
        }

        return $value;
    }

    /**
     * Determine if the provided value is a valid phone number.
     *
     * Usage: '<index>' => 'phone_number'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     *
     * Examples:
     *
     *  555-555-5555: valid
     *  5555425555: valid
     *  555 555 5555: valid
     *  1(519) 555-4444: valid
     *  1 (519) 555-4422: valid
     *  1-555-555-5555: valid
     *  1-(555)-555-5555: valid
     */
    protected function validate_phone_number($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        $regex = '/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i';
        if (!preg_match($regex, $input[$field])) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Custom regex validator.
     *
     * Usage: '<index>' => 'regex,/your-regex-expression/'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_regex($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        $regex = $param;
        if (!preg_match($regex, $input[$field])) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * JSON validator.
     *
     * Usage: '<index>' => 'valid_json_string'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_json_string($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!is_string($input[$field]) || !is_object(json_decode($input[$field]))) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Check if an input is an array and if the size is more or equal to a specific value.
     *
     * Usage: '<index>' => 'valid_array_size_greater,1'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_array_size_greater($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!is_array($input[$field]) || sizeof($input[$field]) < (int)$param) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Check if an input is an array and if the size is less or equal to a specific value.
     *
     * Usage: '<index>' => 'valid_array_size_lesser,1'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_array_size_lesser($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!is_array($input[$field]) || sizeof($input[$field]) > (int)$param) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Check if an input is an array and if the size is equal to a specific value.
     *
     * Usage: '<index>' => 'valid_array_size_equal,1'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_array_size_equal($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!is_array($input[$field]) || sizeof($input[$field]) == (int)$param) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }


    /**
     * Determine if the input is a valid person name in Persian/Dari or Arabic mainly in Afghanistan and Iran.
     *
     * Usage: '<index>' => 'valid_persian_name'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_persian_name($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match("/^([ا آ أ إ ب پ ت ث ج چ ح خ د ذ ر ز ژ س ش ص ض ط ظ ع غ ف ق ک ك گ ل م ن و ؤ ه ة ی ي ئ ء ّ َ ِ ُ ً ٍ ٌ ْ\x{200B}-\x{200D}])+$/u", $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the input is a valid person name in English, Persian/Dari/Pashtu or Arabic mainly in Afghanistan and Iran.
     *
     * Usage: '<index>' => 'valid_eng_per_pas_name'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_eng_per_pas_name($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match("/^([A-Za-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝàáâãäåçèéêëìíîïñðòóôõöùúûüýÿ'\- ا آ أ إ ب پ ت ټ ث څ ج چ ح ځ خ د ډ ذ ر ړ ز ږ ژ س ش ښ ص ض ط ظ ع غ ف ق ک ګ ك گ ل م ن ڼ و ؤ ه ة ی ي ې ۍ ئ ؋ ء ّ َ ِ ُ ً ٍ ٌ ْ \x{200B}-\x{200D} \s])+$/u", $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the input is valid digits in Persian/Dari, Pashtu or Arabic format.
     *
     * Usage: '<index>' => 'valid_persian_digit'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_persian_digit($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match("/^([۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩])+$/u", $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }


    /**
     * Determine if the input is a valid text in Persian/Dari or Arabic mainly in Afghanistan and Iran.
     *
     * Usage: '<index>' => 'valid_persian_text'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_persian_text($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match("/^([ا آ أ إ ب پ ت ث ج چ ح خ د ذ ر ز ژ س ش ص ض ط ظ ع غ ف ق ک ك گ ل م ن و ؤ ه ة ی ي ئ ء ّ َ ِ ُ ً ٍ ٌ \. \/ \\ = \- \| \{ \} \[ \] ؛ : « » ؟ > < \+ \( \) \* ، × ٪ ٫ ٬ ! ۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩\x{200B}-\x{200D} \x{FEFF} \x{22} \x{27} \x{60} \x{B4} \x{2018} \x{2019} \x{201C} \x{201D} \s])+$/u", $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the input is a valid text in Pashtu mainly in Afghanistan.
     *
     * Usage: '<index>' => 'valid_pashtu_text'
     *
     * @param string $field
     * @param array $input
     *
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_pashtu_text($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }

        if (!preg_match("/^([ا آ أ ب پ ت ټ ث څ ج چ ح ځ خ د ډ ذ ر ړ ز ږ ژ س ش ښ ص ض ط ظ ع غ ف ق ک ګ ل م ن ڼ و ؤ ه ة ی ې ۍ ي ئ ء ْ ٌ ٍ ً ُ ِ َ ّ ؋ \. \/ \\ = \- \| \{ \} \[ \] ؛ : « » ؟ > < \+ \( \) \* ، × ٪ ٫ ٬ ! ۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩ \x{200B}-\x{200D} \x{FEFF} \x{22} \x{27} \x{60} \x{B4} \x{2018} \x{2019} \x{201C} \x{201D} \s])+$/u", $input[$field]) !== false) {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

    /**
     * Determine if the provided value is a valid twitter handle.
     *
     * @access protected
     * @param string $field
     * @param array $input
     * @param null $param
     * @return mixed
     */
    protected function validate_valid_twitter($field, $input, $param = null) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return null;
        }
        $json_twitter = file_get_contents("http://twitter.com/users/username_available?username=" . $input[$field]);

        $twitter_response = json_decode($json_twitter);
        if ($twitter_response->reason != "taken") {
            return [
                'field' => $field,
                'value' => $input[$field],
                'rule'  => __FUNCTION__,
                'param' => $param,
            ];
        }
        return null;
    }

}
