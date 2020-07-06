<?php
namespace Traffic\Actions\Predefined;
use Traffic\Actions\AbstractAction;

class Frame extends AbstractAction
{
    protected $_weight = 200;

    public function getType()
    {
        return self::TYPE_REDIRECT;
    }

    protected function _execute()
    {
        $this->_executeInContext();
    }

    protected function _executeDefault()
    {
        $url = $this->getActionPayload();
        $this->setDestinationInfo($url);
        $this->setContent("<html>
            <head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\" /></head>
            <frameset rows=\"100%\"><frame src=\"".$url."\"></frameset></html>");
    }
}
