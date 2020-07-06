<?php
namespace Traffic\Macros\Predefined;

use Core\Currency\Service\CurrencyService;
use Traffic\Model\BaseStream;
use Traffic\Macros\AbstractConversionMacro;
use Traffic\Model\Conversion;

class ConversionRevenue extends AbstractConversionMacro
{
    public function process(BaseStream $stream, Conversion $conversion, $code = null)
    {
        if (empty($code)) {
            return $conversion->getRevenue();
        }
        return CurrencyService::instance()->exchangeToCurrent($conversion->getRevenue(), $code);
    }
}