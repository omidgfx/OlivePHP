<?php namespace Olive\MVC;

use Olive\Exceptions\LayoutNotFoundException;
use Olive\Exceptions\ViewEngineException;
use Olive\Exceptions\ViewNotFoundException;
use Olive\ViewEngine\ViewEngine;

class ViewResult extends ActionResult
{

    /** @var string */
    private $view;

    /** @var Controller */
    private $controller;

    /**
     * ViewResult constructor.
     * @param string $view
     */
    public function __construct(string $view = null) {
        $this->view = $view;
    }


    /**
     * @param Controller $controller
     * @throws LayoutNotFoundException
     * @throws ViewEngineException
     * @throws ViewNotFoundException
     */
    public function executeResult(Controller $controller) {
        $this->controller = $controller;
        $this->view       = $this->findView();
        ViewEngine::render($this->view, ['context' => $this]);
    }

    protected function findView() {
        return $this->view ?? $this->controller->route->controller;
    }

    #region Setters & Getters

    /**
     * @param string $view
     * @return $this
     */
    public function setView(string $view) {
        $this->view = $view;
        return $this;
    }

    /**
     * @return string
     */
    public function getView(): string {
        return $this->view;
    }

    /**
     * @return Controller
     */
    public function getController() {
        return $this->controller;
    }

    public function e($expression) {

    }
}
