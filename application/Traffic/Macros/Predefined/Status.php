<?php
namespace Traffic\Macros\Predefined;

use Traffic\RawClick;
use Traffic\Model\Conversion;
use Traffic\Macros\AbstractConversionMacro;
use Traffic\Model\BaseStream;
use Traffic\Macros\AbstractClickMacro;

class Status extends AbstractConversionMacro
{
    public function process(BaseStream $stream, Conversion $conversion, $mapping = null)
    {
        if (!empty($mapping)) {
            $items = explode(' ', $mapping);
            foreach ($items as $item) {
                $param = explode('=', $item);
                if (@$param[0] == $conversion->get('status')) {
                    return @$param[1];
                }
            }
        } else {
            return $conversion->get('status');
        }
    }
}