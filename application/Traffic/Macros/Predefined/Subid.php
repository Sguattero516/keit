<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class Subid extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $raw, $xSeparator = false)
    {
        $subid = $raw->getSubId();
        if ($xSeparator) {
            $subid = str_replace('-', 'x', $subid);
        }

        return $subid;
    }
}