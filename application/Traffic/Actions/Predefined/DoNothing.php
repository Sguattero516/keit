<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;

class DoNothing extends AbstractAction
{
    protected $_weight = 100;

    const DO_NOTHING = 'Do nothing';

    public function getType()
    {
        return self::TYPE_OTHER;
    }

    public function getField()
    {
        return self::NOTHING;
    }

    public function _execute()
	{
	    $this->setDestinationInfo(self::DO_NOTHING);
	}
}
