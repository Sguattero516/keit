<?php
namespace Redirects;

use Traffic\Actions\AbstractAction;

class example extends AbstractAction
{
    protected $_name = 'Example';
    protected $_weight = 100;

    protected function _execute()
    {
        /**
          To get any resource, use these methods:
          - $rawClick = $this->getRawClick();
          - $stream = $this->getStream();
          - $campaign = $this->getCampaign();
          - $landing = $this->getLanding();
          - $offer = $this->getOffer();
          - $request = $this->getRequest();

          To get info about current action data:
          - $this->getActionPayload();
          - $this->getRawActionPayload();
          - $this->getActionOptions();

          To perform action:
          - $this->addHeader($headerLine);
          - $this->redirect($url);
          - $this->setContent($text);
          - $this->setContentType($contentType);
          - $this->setStatus($httpStatusCode);

          To set info about the destination:
            $this->setDestinationInfo($string);

          To parse macros in string:
            $this->processMacros($string);
        **/


        $this->addHeader("Location: " . $this->getActionPayload());
    }
}
