<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class Date extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick, $format = 'c')
    {
        return $rawClick->getDateTime()->format($format);
    }
}