<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;

class BlankReferrer extends AbstractAction
{
    protected $_weight = 3;

    protected function _execute()
    {
        $this->_executeInContext();
    }

    protected function _executeDefault()
    {
        $url = $this->getActionPayload();

        $code = '<script>
            function go() {
               window.frames[0].document.body.innerHTML = \'<form target="_parent" method="post" action="' . $url . '"></form>\';
                window.frames[0].document.forms[0].submit()
            }
        </script>
        <iframe onload="window.setTimeout(\'go()\', 99)" src="about:blank" style="visibility:hidden"></iframe>';
        $this->setContent($code);
        $this->setDestinationInfo($url);
    }

    protected function _executeForScript()
    {
        $url = $this->getActionPayload();
        $cid = $this->getServerRequest()->getParam('_cid');
        $this->setContentType('application/javascript');

        $frame = '<iframe id=\"frm\" sandbox=\"allow-top-navigation allow-scripts allow-popups allow-forms\" onload=\'window.setTimeout(\"go()\", 99)\' src=\'about:blank\' style=\'visibility:hidden\'></iframe>';

        $code = '';

        if ($cid) {
            $code .= '
            var el = document.getElementById("'.$cid.'");
            el.innerHTML = "'.$frame.'";
            document.getElementById(\'frm\').src = \'data:text/html,\' + encodeURIComponent(\'<form target="_parent" method="post" action="' . $url . '"></form><script>document.forms[0].submit()<\/script>\');
            ';

        } else {
            $code .= 'document.write("'.$frame.'");';
            $code .= '
            function go() {
                window.frames[0].document.body.innerHTML = \'<form target="_parent" method="post" action="' . $url . '"></form>\';
                window.frames[0].document.forms[0].submit();
            }';
        }

        $this->setContent($code);
        $this->setDestinationInfo($url);
    }

    protected function _executeForFrame()
    {
        $url = $this->getActionPayload();
        $this->setDestinationInfo($url);

        $code = '
            <iframe id="frm" sandbox="allow-top-navigation allow-scripts allow-popups allow-forms"  src=\'about:blank\' style=\'visibility:hidden\'></iframe>
            <script>
            function go() {
                document.getElementById(\'frm\').src = \'data:text/html,\' + encodeURIComponent(\'<form target="_parent" method="post" action="' . $url . '"></form><script>document.forms[0].submit()<\/script>\');
            }
            window.setTimeout("go()", 99)</script>';

        $this->setContent($code);
        $this->setDestinationInfo($url);
    }

}
