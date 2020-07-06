<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;
use Traffic\Actions\AdsParser;
use Traffic\Actions\Service\RedirectService;

class Meta extends AbstractAction
{

    protected $_weight = 2;

    protected function _execute()
	{
        $this->setDestinationInfo($this->getActionPayload());
        $this->_executeInContext();
	}

	protected function _executeDefault()
	{
        $code = RedirectService::instance()->metaRedirect($this->getActionPayload());
        $this->setContent($code);
	}

    protected function _executeForScript()
    {
        $code = RedirectService::instance()->metaRedirect($this->getActionPayload());

        $this->setContentType('application/javascript');
        $parser = new AdsParser($code, $this->getServerRequest()->getParam('_cid'));
        $code = $parser->getCode();
        $this->setContent($code);
    }
}
