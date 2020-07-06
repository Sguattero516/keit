<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class Random extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick)
    {
        switch (func_num_args()){
            case 2:
                $min = 0;
                $max = 9999;
                break;
            case 3:
                $min = 1;
                $max = func_get_arg(2);
                break;
            case 4:
                $min = func_get_arg(2);
                $max = func_get_arg(3);
                break;
        }

        return rand((int) $min, (int) $max);
    }
}