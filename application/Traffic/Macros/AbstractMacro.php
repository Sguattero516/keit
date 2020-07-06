<?php
namespace Traffic\Macros;

use Traffic\Request\ServerRequest;

abstract class AbstractMacro
{
    /**
     * @var ServerRequest
     */
    private $_serverRequest;

    public function alwaysRaw()
    {
        return false;
    }

    public function getServerRequest()
    {
        return $this->_serverRequest;
    }

    public function setServerRequest(ServerRequest $serverRequest)
    {
        $this->_serverRequest = $serverRequest;
    }
}