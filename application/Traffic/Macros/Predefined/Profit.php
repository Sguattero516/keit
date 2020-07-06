<?php
namespace Traffic\Macros\Predefined;

use Core\Currency\Service\CurrencyService;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;
use Traffic\Model\BaseStream;

class Profit extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick, $code = null)
    {
        if (empty($code)) {
            return $rawClick->getProfit();
        }
        return CurrencyService::instance()->exchangeToCurrent($rawClick->getProfit(), $code);
    }
}