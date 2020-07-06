<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;
use Traffic\Actions\Service\StreamActionService;

class ShowText extends AbstractAction
{
    protected $_weight = 100;

    public function getType()
    {
        return self::TYPE_OTHER;
    }

    public function getField()
    {
        return self::TEXT;
    }

    public function _execute()
	{
        $this->_executeInContext();
        $this->setDestinationInfo(StreamActionService::instance()->truncateDestination($this->getResponse()->getBody()));
	}

    public function _executeForScript()
    {
        $this->setContentType('application/javascript');
        $this->setContent($this->buildContent());
    }

    public function _executeForFrame()
    {
        $this->setContent($this->buildContent());
    }

    public function _executeDefault()
    {
        $this->setContent($this->buildContent());
    }

    private function buildContent()
    {
        return $this->processMacros($this->getPipelinePayload()->getActionPayload());
    }
}
