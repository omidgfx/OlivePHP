<?php namespace Olive\Http\Results;

class EmptyResult extends ActionResult
{
    public function executeResult(): void {
        # send headers
        $this->executeHeaders($this->getHeaders());
    }
}
