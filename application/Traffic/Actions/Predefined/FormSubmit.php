<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;

class FormSubmit extends AbstractAction
{
    protected $_weight = 5;

    protected $_delay = 0;  // задержка перед сабмитом формы (сек)

    protected function _execute()
    {
        $content = '<!doctype html>' . PHP_EOL;
        $content .= '<head>' . PHP_EOL;
        $content .= '<script>window.onload = function(){
                setTimeout(function() {
                    document.forms[0].submit();
                }, ' . ($this->_delay * 1000). ');
            };</script>' . PHP_EOL;
        $content .= '</head><body>' . PHP_EOL;
        $content .= '<form action="' . $this->getActionPayload() . '" method="POST">';

        foreach ($this->getServerRequest()->getParsedBody() as $name => $value) {
            $content .= '<input type="hidden" name="' . $name. '" value="' . $value . '" />' . PHP_EOL;
        }
        $content .= '</form>'. PHP_EOL;
        $content .= '</body></html>'. PHP_EOL;
        $this->setContent($content);
    }
}