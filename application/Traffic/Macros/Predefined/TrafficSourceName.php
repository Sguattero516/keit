<?php
namespace Traffic\Macros\Predefined;

use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;
use Traffic\Model\BaseStream;
use Traffic\Repository\CachedTrafficSourceRepository;

class TrafficSourceName extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick)
    {
        if ($rawClick->getTsId()) {
            $ts = CachedTrafficSourceRepository::instance()->findCached($rawClick->getTsId());
            if ($ts) {
                return $ts->getName();
            }
        }

        return '';
    }
}