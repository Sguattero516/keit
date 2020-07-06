<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;
use Traffic\Actions\Service\RedirectService;

class JsForIframe extends AbstractAction
{
    protected $_weight = 999;

    protected function _execute()
    {
        $this->_executeInContext();
    }

    protected function _executeForFrame()
    {
        $url = $this->getActionPayload();
        $this->setDestinationInfo($url);
        $this->setContent(RedirectService::instance()->frameRedirect($url));
    }

    protected function _executeDefault()
    {
        $this->_executeForFrame();
    }
}