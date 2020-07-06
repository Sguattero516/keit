<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\Macros\AbstractConversionMacro;
use Traffic\Model\Conversion;

class ConversionTime extends AbstractConversionMacro
{
    public function process(BaseStream $stream, Conversion $conversion, $format = 'c')
    {
        return $conversion->getPostbackDatetime()->format($format);
    }
}