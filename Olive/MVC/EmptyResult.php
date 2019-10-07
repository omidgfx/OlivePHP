<?php namespace Olive\MVC;

use Olive\Traits\Singleton;

class EmptyResult extends ActionResult
{
    use Singleton;

    protected function _singletonConstruct() { }

    public function executeResult(Controller $controller) {

        # send headers
        $this->executeHeaders($this->getHeaders());
    }
}
