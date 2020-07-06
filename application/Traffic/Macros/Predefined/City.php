<?php
namespace Traffic\Macros\Predefined;

use Traffic\GeoDb\IpInfoType;
use Traffic\GeoDb\Repository\GeoDbRepository;
use Traffic\GeoDb\Service\GeoDbService;
use Traffic\Macros\AbstractClickMacro;
use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Core\Locale\LocaleService;
use Traffic\Logging\Service\LoggerService;

class City extends AbstractClickMacro
{
    const RU = 'ru';

    public function process(BaseStream $stream, RawClick $rawClick, $lang = null)
    {
        if (empty($lang)) {
            $lang = LocaleService::instance()->getLanguage();
        }

        if ($lang == self::RU) {
            $db = GeoDbRepository::instance()->getDbForDataType(IpInfoType::CITY_RU);
            if (empty($db)) {
                return $rawClick->getCity() ?? '';
            }
            $info = GeoDbService::instance()->info($db, $rawClick->getIpString());
            return isset($info[IpInfoType::CITY_RU]) ? $info[IpInfoType::CITY_RU] : null;
        } else {
            return $rawClick->getCity() ?? '';
        }
    }
}