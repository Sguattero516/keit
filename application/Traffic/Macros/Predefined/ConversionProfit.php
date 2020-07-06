<?php
namespace Traffic\Macros\Predefined;

use Core\Currency\Service\CurrencyService;
use Traffic\Model\BaseStream;
use Traffic\Macros\AbstractConversionMacro;
use Traffic\Model\Conversion;

class ConversionProfit extends AbstractConversionMacro
{
    public function process(BaseStream $stream, Conversion $conversion, $code = null)
    {
        if (empty($code)) {
            return $conversion->getProfit();
        }
        return CurrencyService::instance()->exchangeToCurrent($conversion->getProfit(), $code);
    }
}