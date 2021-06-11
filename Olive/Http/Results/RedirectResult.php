<?php namespace Olive\Http\Results;

use Olive\Contracts\URL;

class RedirectResult extends ActionResult
{

    /**
     * RedirectResult constructor.
     * @param string|URL $location
     * @param int $statusCode
     */
    public function __construct(private string|URL $location, int $statusCode = 302) {
        $this->setStatusCode($statusCode);
    }

    /**
     * @return string
     */
    public function getLocation(): string {
        return $this->location;
    }

    /**
     * @param string $location
     * @return RedirectResult
     */
    public function setLocation(string $location): RedirectResult {
        $this->location = $location;
        return $this;
    }


    public function executeResult(): void {
        # send headers
        $this->executeHeaders($this->getHeaders());
    }
}
