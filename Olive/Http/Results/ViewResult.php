<?php namespace Olive\Http\Results;

use Olive\Contracts\ViewEngine;
use Olive\Exceptions\LayoutNotFoundException;
use Olive\Exceptions\ViewEngineException;
use Olive\Exceptions\ViewNotFoundException;

class ViewResult extends ActionResult
{

    private array $bag = [];


    /**
     * ViewResult constructor.
     * @param string $view
     */
    public function __construct(private string $view) {
    }

    public function putContext($key, $value): self {
        $this->{$key} = $value;
        return $this;
    }


    /**
     * @throws LayoutNotFoundException
     * @throws ViewEngineException
     * @throws ViewNotFoundException
     */
    public function executeResult(): void {
        $this->executeHeaders($this->getHeaders());
        ViewEngine::render($this->view, ['context' => $this]);
    }

    #region Setters & Getters

    /**
     * @param string $view
     * @return $this
     */
    public function setView(string $view): static {
        $this->view = $view;
        return $this;
    }

    /**
     * @return string
     */
    public function getView(): string {
        return $this->view;
    }


    public function __get($name) {
        return $this->bag[$name] ?? null;
    }

    public function __set($name, $value) {
        $this->bag[$name] = $value;
    }

    public function __isset($name): bool {
        return array_key_exists($name, $this->bag);
    }

}
