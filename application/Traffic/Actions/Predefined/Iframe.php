<?php
namespace Traffic\Actions\Predefined;
use Traffic\Actions\AbstractAction;

class Iframe extends AbstractAction
{
    protected $_weight = 6;

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
        $html = '<!DOCTYPE html>
        <html>
        <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        </head>   
        <style type="text/css">
        body, html{
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling:touch
        }
        iframe {
                width: 100%;
                height:100%;
                min-height: 10000px;
                border: 0;
            }
        </style>
        <body><iframe src="' . $url . '"></iframe></body>
        </html>';
        $this->setContent($html);
        $this->setDestinationInfo($url);
    }

    protected function _executeForFrame()
    {
        $url = $this->getActionPayload();
        $this->addHeader("Location: " . $url);

        if ($this->getServerRequest() && version_compare($this->getServerRequest()->getParam('kversion'), '3.4') >= 0) {
            $this->setStatus(302);
        }
        $this->setDestinationInfo($url);
    }
}
