<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;

class HttpRedirect extends AbstractAction
{
    protected $_weight = 1;

    protected function _execute()
	{
	    $url = $this->getActionPayload();
		$this->addHeader("Location: " . $url);

		if ($this->getServerRequest()->getParam('kversion')) {
            if ($this->getServerRequest() && version_compare($this->getServerRequest()->getParam('kversion'), '3.4') >= 0) {
                $this->setStatus(302);
            }
        } else {
            $this->setStatus(302);
        }
		$this->setDestinationInfo($url);
	}
}
