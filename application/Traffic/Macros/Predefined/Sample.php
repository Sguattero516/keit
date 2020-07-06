<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class Sample extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $visitor, $var1 = null, $var2 = null, $varN = null)
    {
        $params = func_get_args();
        if (count($params) <=2) {
            return '';
        }
        $key = rand(2, count($params) - 1);
        if (is_string($params[$key])) {
            return trim($params[$key]);
        }
        return '';
    }
}