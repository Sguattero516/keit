<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;

class Status404 extends AbstractAction
{
    protected $_weight = 12;

    const DESTINATION = '404 Not Found';

    const NOT_FOUND = 'HTTP/1.1 404 Not Found';

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
        $this->setStatus(404);
        $this->setDestinationInfo(self::DESTINATION);
    }
}