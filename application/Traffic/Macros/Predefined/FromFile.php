<?php
namespace Traffic\Macros\Predefined;

use Traffic\Macros\AbstractClickMacro;
use Traffic\Model\BaseStream;
use Traffic\Tools\Tools;
use Traffic\RawClick;

class FromFile extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $visitor, $file)
    {
        $file = Tools::sanitizeFilename($file);
        return trim(file_get_contents(ROOT . '/var/macros/' . $file));
    }
}