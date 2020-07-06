<?php
namespace Traffic\Macros\Predefined;

use Component\GeoDb\Repository\ConnectionTypesRepository;
use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class ConnectionType extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick, $lang = null)
    {
        if (empty($lang)) {
            return $rawClick->getConnectionType() ?? '';
        }
        return ConnectionTypesRepository::instance()->getName($rawClick->getConnectionType(), $lang);
    }
}