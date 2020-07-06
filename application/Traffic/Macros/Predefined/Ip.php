<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class Ip extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $visitor)
    {
        return $visitor->getIpString();
    }
}