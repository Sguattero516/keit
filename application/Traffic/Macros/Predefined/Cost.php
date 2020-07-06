<?php
namespace Traffic\Macros\Predefined;

use Core\Currency\Service\CurrencyService;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;
use Traffic\Model\BaseStream;

class Cost extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick, $code = null)
    {
        if (empty($code)) {
            return $rawClick->getCost();
        }
        return CurrencyService::instance()->exchangeToCurrent($rawClick->getCost(), $code);
    }
}