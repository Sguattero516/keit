<?php
namespace Traffic\Macros\Predefined;

use Traffic\Macros\AbstractClickMacro;
use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Repository\CachedSettingsRepository;

class Currency extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick)
    {
        return CachedSettingsRepository::instance()->get('currency');
    }
}