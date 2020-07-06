<?php
namespace Traffic\Macros\Predefined;

use Component\GeoDb\Repository\OperatorsRepository;
use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class Operator extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick, $lang = null)
    {
        if (empty($lang)) {
            return $rawClick->getOperator() ?? '';
        }
        return OperatorsRepository::instance()->getName($rawClick->getOperator(), $lang);
    }
}