<?php
namespace Traffic\Macros\Predefined;

use Traffic\Macros\AbstractConversionMacro;
use Traffic\Model\BaseStream;
use Traffic\Model\Conversion;

class Tid extends AbstractConversionMacro
{
    public function process(BaseStream $stream, Conversion $conversion)
    {
        return $conversion->getTid() ?? '';
    }
}