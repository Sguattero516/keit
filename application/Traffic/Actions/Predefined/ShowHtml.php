<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;
use Traffic\Actions\AdsParser;
use Traffic\Actions\Service\StreamActionService;

class ShowHtml extends AbstractAction
{
    public function getField()
    {
        return self::TEXT;
    }

    protected $_weight = 100;

    public function getType()
    {
        return self::TYPE_OTHER;
    }

    protected function _execute()
    {
        $this->_executeInContext();
    }

    protected function _executeForScript()
    {
        $this->setContentType('application/javascript');
        $parser = new AdsParser($this->buildContent(), $this->getServerRequest()->getParam('_cid'));
        $code = $parser->getCode();
        $this->setDestinationInfo(StreamActionService::instance()->truncateDestination($code));
        $this->setContent($code);
    }
    protected function _executeForFrame()
    {
        $this->setContentType('text/html');
        $code = $this->buildContent();

        if (!stristr($code, '<html')) {
            $code = '<html><style>body{margin:0}</style><body>' . $code . '</body></html>';
        }
        $this->setDestinationInfo(StreamActionService::instance()->truncateDestination($code));
        $this->setContent($code);
    }

    protected function _executeDefault()
    {
        $this->_executeForFrame();
    }

    private function buildContent()
    {
        return $this->processMacros($this->getPipelinePayload()->getActionPayload());
    }
}