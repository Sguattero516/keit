<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class AnyClickMacro extends AbstractClickMacro
{
    private $_paramName;

    public function __construct($paramName)
    {
        $this->_paramName = $paramName;
    }

    public function process(BaseStream $stream, RawClick $rawClick)
    {
          return $rawClick->get($this->_paramName) ?? '';
    }
}