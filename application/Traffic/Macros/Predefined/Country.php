<?php
namespace Traffic\Macros\Predefined;

use Component\GeoDb\Repository\CountriesRepository;
use Core\Locale\LocaleService;
use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class Country extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick, $lang = null)
    {
        if (empty($lang)) {
            return $rawClick->getCountry() ??    '';
        }

        if (!in_array($lang, [LocaleService::EN, LocaleService::RU])) {
            return null;
        }

        return CountriesRepository::instance()->getCountryName($rawClick->getCountry(), $lang);
    }
}