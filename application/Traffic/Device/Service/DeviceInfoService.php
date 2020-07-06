<?php
namespace Traffic\Device\Service;

use Traffic\Cache\CacheService;
use Traffic\Model\Setting;
use Traffic\Device\DeviceInfoType;
use Traffic\Device\DeviceType;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Service\SettingsService;
use Traffic\Logging\Service\LoggerService;
use Traffic\Service\AbstractService;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

class DeviceInfoService extends AbstractService
{

    private $_exceptManufactures = array(
        'Apple', 'RIM'
    );

    /**
     * @var DeviceDetector
     */
    private $_detector;

    private $_matches = [
            DeviceType::DEVICE_DESKTOP => DeviceParserAbstract::DEVICE_TYPE_DESKTOP,
            DeviceType::DEVICE_MOBILE => DeviceParserAbstract::DEVICE_TYPE_SMARTPHONE,
            DeviceType::DEVICE_TABLET => DeviceParserAbstract::DEVICE_TYPE_TABLET,
            DeviceType::DEVICE_CONSOLE => DeviceParserAbstract::DEVICE_TYPE_CONSOLE,
            DeviceType::DEVICE_TV => DeviceParserAbstract::DEVICE_TYPE_TV,
            DeviceType::DEVICE_CAR_BROWSER => DeviceParserAbstract::DEVICE_TYPE_CAR_BROWSER,
            DeviceType::DEVICE_SMART_DISPLAY => DeviceParserAbstract::DEVICE_TYPE_SMART_DISPLAY,
            DeviceType::DEVICE_CAMERA => DeviceParserAbstract::DEVICE_TYPE_CAMERA,
            DeviceType::DEVICE_PORTABLE_MEDIA_PLAYER => DeviceParserAbstract::DEVICE_TYPE_PORTABLE_MEDIA_PAYER,
    ];

    private $_deviceTypeReplacements = [
        'smartphone' => 'mobile',
        'feature_phone' => 'mobile',
        'phablet' => 'mobile'
    ];

    private $_osReplacements = [
        'Mac' => 'OS X',
        'MTK / Nucleus' => 'MTK',
        'PlayStation Portable' => 'PS',
        'PS Portable' => 'PS',
        'Nintendo Mobile' => 'Nintendo'
    ];

    public function getExceptManufactures()
    {
        return $this->_exceptManufactures;
    }

    public function info($ua)
    {
        DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);

        $toCheckBot = CachedSettingsRepository::instance()->get(Setting::CHECK_BOT_UA);

        $this->_init();
        $this->_detector->skipBotDetection(!$toCheckBot);
        $this->_detector->setUserAgent($ua);
        try {
            $this->_detector->parse();
        } catch (\Exception $e) {
            LoggerService::instance()->warning($e->getMessage());
        }

        $browser = $this->_detector->getClient();
        $os = $this->_detector->getOs();

        $data = [
            DeviceInfoType::BROWSER => isset($browser['name']) ? $browser['name'] : null,
            DeviceInfoType::BROWSER_VERSION => isset($browser['version']) ? $browser['version'] : null,
            DeviceInfoType::OS => $this->_convertOs(isset($os['name']) ? $os['name'] : null),
            DeviceInfoType::OS_VERSION => isset($os['version']) ? $os['version'] : null,
            DeviceInfoType::DEVICE_TYPE => $this->_convertDeviceType($this->_detector),
            DeviceInfoType::DEVICE_BRAND => $this->_detector->getBrandName(),
            DeviceInfoType::DEVICE_MODEL => $this->_convertDeviceModel($this->_detector->getBrandName(), $this->_detector->getModel())
        ];

        if ($toCheckBot) {
           $data[DeviceInfoType::IS_BOT] = $this->_detector->isBot();
        }

        return $data;
    }


    private function _convertDeviceModel($deviceBrand, $deviceModel)
    {
        $name = '';
        if (!empty($deviceBrand)) {
            if (!in_array($deviceBrand, $this->_exceptManufactures)) {
                $name = $deviceBrand;
            }
            if (isset($deviceModel)) {
                if ($name) {
                    $name .= ' ';
                }
                $name .= $deviceModel;
            }
        }

        return $name;
    }

    private function _convertOs($os)
    {
        if (isset($this->_osReplacements[$os])) {
            return $this->_osReplacements[$os];
        } else {
            return $os;
        }
    }

    protected function _convertDeviceType(DeviceDetector $dd)
    {
        $deviceType = null;
        $id = $dd->getDevice();

        if ($id == DeviceParserAbstract::DEVICE_TYPE_PHABLET) {
            $id = DeviceParserAbstract::DEVICE_TYPE_SMARTPHONE;
        }

        if ($id == DeviceParserAbstract::DEVICE_TYPE_FEATURE_PHONE) {
            $id = DeviceParserAbstract::DEVICE_TYPE_SMARTPHONE;
        }

        if ($id !== null) {
            $deviceType = array_search($id, $this->_matches);
        }

        if (empty($deviceType)) {
            if (strstr($dd->getOs('name'), 'Android') || stristr($dd->getOs('name'), 'Phone')
                || $dd->getOs('name') == 'Windows CE' || $dd->getOs('name') == 'Symbian OS'
            ) {
                $deviceType = DeviceType::DEVICE_MOBILE;
            }

            if (strstr($dd->getClient('name'), 'Mobile') || strstr($dd->getClient('name'), 'Mini')) {
                $deviceType = DeviceType::DEVICE_MOBILE;
            }
        }

        if (isset($this->_deviceTypeReplacements[$deviceType])) {
            return $this->_deviceTypeReplacements[$deviceType];
        }
        return $deviceType;
    }

    private function _init()
    {
        if (!isset($this->_detector)) {
            $this->_detector = new DeviceDetector();
            $this->_detector->setCache(CacheService::instance()->deviceDetectorCache()->getDoctrineCache());
            $this->_detector->discardBotInformation();
        }
    }
}