<?php
namespace Traffic\Macros\Predefined;

use Traffic\RawClick;
use Traffic\Model\Conversion;
use Traffic\Macros\AbstractConversionMacro;
use Traffic\Model\BaseStream;
use Traffic\Macros\AbstractClickMacro;

class OriginalStatus extends AbstractConversionMacro
{
    public function process(BaseStream $stream, Conversion $conversion)
    {
        return $conversion->get('original_status');
    }
}