<?php
namespace Macros;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

/**
 * This is an example of a macro
 */
class example extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $click, $charset = 'utf-8')
    {
        $badWords = ['one', 'two', 'three'];
        $keyword = $click->getKeyword();
        $keyword  = str_replace($badWords, '', $keyword);
        return $keyword ;
    }
}