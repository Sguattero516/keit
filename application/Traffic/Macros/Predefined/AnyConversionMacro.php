<?php
namespace Traffic\Macros\Predefined;

use Traffic\Macros\AbstractConversionMacro;
use Traffic\Model\BaseStream;
use Traffic\Model\Conversion;

class AnyConversionMacro extends AbstractConversionMacro
{
    private $_paramName;

    public function __construct($paramName)
    {
        $this->_paramName = $paramName;
    }

    public function process(BaseStream $stream, Conversion $conversion)
    {
        return $conversion->get($this->_paramName) ?? '';
    }
}