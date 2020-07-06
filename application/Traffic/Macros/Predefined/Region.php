<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Component\GeoDb\Repository\RegionsRepository;
use Traffic\Macros\AbstractClickMacro;

class Region extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick, $lang = 'en')
    {
        if (empty($lang)) {
            return $rawClick->getRegion() ?? '';
        }
        return RegionsRepository::instance()->getRegionName($rawClick->getRegion(), $lang);
    }
}