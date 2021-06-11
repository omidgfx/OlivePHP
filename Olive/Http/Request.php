<?php namespace Olive\Http;


use JsonSerializable;
use Olive\Contracts\Support\Arrayable;
use Olive\Contracts\Validator\Validator;
use Olive\Exceptions\BadRequestException;
use stdClass;

class Request extends stdClass implements Arrayable, JsonSerializable
{
    private array $bodyJSON = [];
    private array $headers  = [];

    protected Validator $validator;

    public function __construct(protected $extraInputs = []) {
        $this->validator = new Validator;
    }

    /**
     * @throws \Olive\Exceptions\BadRequestException
     */
    public function validate($rules = null, $messages = []): void {

        $validator  = $this->validator;
        $validation = $validator->validate($this->toArray(), $rules ?? [], $messages);

        if ($validation->fails()) {
            $errors = $validation->errors()->toArray();
            $errors = array_map('array_shift', $errors);
            throw BadRequestException::make($errors);
        }
    }

    private function initBodyJSON(): void {
        if ($this->bodyJSON !== null)
            return;
        $inputJSON      = file_get_contents('php://input');
        $this->bodyJSON = @json_decode($inputJSON, TRUE) ?? [];
        $this->bodyJSON = array_merge($_REQUEST, $this->bodyJSON);
        $this->bodyJSON = $this->bodyJSON ?? [];
    }

    private function initHeaders(): void {
        if ($this->headers !== null)
            return;
        $this->headers = getallheaders();
    }

    public function file($name, $fallback = null) {
        return $_FILES[$name] ?? $fallback;
    }

    public function input($name, $fallback = null) {
        return $_POST[$name] ?? $fallback;
    }

    public function query($name, $fallback = null) {
        return $_GET[$name] ?? $fallback;
    }

    public function body($name, $fallback = null) {
        $this->initBodyJSON();
        return $this->bodyJSON[$name] ?? $fallback;
    }

    public function header($name, $fallback = null) {
        $this->initHeaders();
        return $this->headers[$name] ?? $fallback;
    }

    public function extra($name, $fallback = null) {
        return $this->extraInputs[$name] ?? $fallback;
    }

    public function all($name, $fallback = null) {
        return $this->toArray()[$name] ?? $fallback;
    }

    public function __get($name) {
        return $this->all($name);
    }

    public function __set($name, $value) {
        $this->{$name} = $value;
    }

    public function __isset($name): bool {
        return !$this->all($name, new stdClass) instanceof stdClass;
    }

    public function toArray(): array {
        $this->initBodyJSON();
        $this->initHeaders();
        return array_merge(
            $_REQUEST,
            $this->bodyJSON,
            $_FILES,
            $this->headers,
            $this->extraInputs
        );
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }
}