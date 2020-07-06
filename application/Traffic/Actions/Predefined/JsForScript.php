<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;
use Traffic\Actions\Service\RedirectService;

class JsForScript extends AbstractAction
{
    protected $_weight = 900;

    protected function _execute()
    {
        $this->_executeInContext();
    }

    protected function _executeForScript()
    {
        $this->setContentType('application/javascript');
        $url = $this->getActionPayload();
        $this->setDestinationInfo($url);
        $this->setContent(RedirectService::instance()->scriptRedirect($url));
    }

    protected function _executeForFrame()
    {
        $this->setContentType('html/text');
        $url = $this->getActionPayload();
        $this->setDestinationInfo($url);
        $this->setContent(RedirectService::instance()->frameRedirect($url));
    }

    protected function _executeDefault()
    {
        $this->_executeForScript();
    }
}