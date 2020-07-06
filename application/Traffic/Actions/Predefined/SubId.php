<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;

class SubId extends AbstractAction
{
    protected $_weight = 140;

    const SUB_ID = 'SubId';

    public function getType()
    {
        return self::TYPE_OTHER;
    }

    public function getField()
    {
        return self::NOTHING;
    }

    protected function _execute()
    {
        $subId = $this->getRawClick()->getSubId();
        if (isset($_GET['return']) && $_GET['return'] == 'jsonp') {
            $this->addHeader('Content-type: application/javascript; charset=utf-8');
            $subId = 'KTracking.response("' . $subId . '")';
        }
        $this->setContent($subId);
        $this->setDestinationInfo(self::SUB_ID . ': '  . $subId);
    }
}