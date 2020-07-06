<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;
use Traffic\Actions\Service\RedirectService;

class Js extends AbstractAction
{
    protected $_weight = 2;

    protected function _execute()
    {
        $this->_executeInContext();
    }

    protected function _executeDefault()
    {
        $url = $this->getActionPayload();
        $this->setDestinationInfo($url);
        $js = $this->_getJavascriptRedirect($url);
        $code = '<html>
        <head>
            <script type="application/javascript">' .$js . '</script>
        </head>
        <body>
            The Document has moved <a href="' . $url . '">here</a>
        </body>
        </html>' ;

        $this->setContent($code);
    }

    protected function _executeForScript()
    {
        $url = $this->getActionPayload();
        $this->setDestinationInfo($url);
        $this->setContentType('application/javascript');
        $js = $this->_getJavascriptRedirect($url);
        $this->setContent($js);
    }

    protected function _executeForFrame()
    {
        $url = $this->getActionPayload();
        $this->setDestinationInfo($url);
        $this->setContent(RedirectService::instance()->frameRedirect($url));
    }

    private function _getJavascriptRedirect($url)
    {
        $code = '
                function process() {
                   if (window.location !== window.parent.location ) {
                      top.location = "' . $url . '";
                   } else {
                      window.location = "' . $url . '";
                   }
                }
                window.onerror = process;
                process();';

        return $code ;
    }
}
