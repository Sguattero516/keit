<?php
namespace Traffic\Macros\Predefined;

use Component\Device\Repository\DeviceTypeRepository;
use Core\Locale\LocaleService;
use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class DeviceType extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick, $lang = null)
    {
        if (empty($lang) || $lang == LocaleService::EN) {
            return $rawClick->getDeviceType() ?? '';
        }
        return DeviceTypeRepository::instance()->getName($rawClick->getDeviceType());
    }
}