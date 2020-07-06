<?php
namespace Traffic\Macros\Predefined;

use Core\Currency\Service\CurrencyService;
use Traffic\RawClick;
use Traffic\Model\BaseStream;
use Traffic\Macros\AbstractClickMacro;

class Revenue extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick, $code = null)
    {
        if (empty($code)) {
            return $rawClick->getRevenue();
        }
        return CurrencyService::instance()->exchangeToCurrent($rawClick->getRevenue(), $code);
    }
}