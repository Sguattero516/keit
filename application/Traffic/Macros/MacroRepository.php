<?php
namespace Traffic\Macros;

use Core\Application\FeatureService;
use Traffic\Macros\Predefined\AnyConversionMacro;
use Traffic\Macros\Predefined\ConversionTime;
use Traffic\Macros\Predefined\Debug;
use Traffic\Model\Click;

use Traffic\BackCompatibility\BackCompatibility;
use Traffic\Macros\Predefined\AnyClickMacro;
use Traffic\Macros\Predefined\CampaignName;
use Traffic\Macros\Predefined\City;
use Traffic\Macros\Predefined\ConnectionType;
use Traffic\Macros\Predefined\Cost;
use Traffic\Macros\Predefined\ConversionCost;
use Traffic\Macros\Predefined\ConversionRevenue;
use Traffic\Macros\Predefined\ConversionProfit;
use Traffic\Macros\Predefined\Country;
use Traffic\Macros\Predefined\Date;
use Traffic\Macros\Predefined\DeviceType;
use Traffic\Macros\Predefined\FromFile;
use Traffic\Macros\Predefined\Ip;
use Traffic\Macros\Predefined\Keyword;
use Traffic\Macros\Predefined\Offer;
use Traffic\Macros\Predefined\Operator;
use Traffic\Macros\Predefined\OriginalStatus;
use Traffic\Macros\Predefined\Profit;
use Traffic\Macros\Predefined\Random;
use Traffic\Macros\Predefined\Region;
use Traffic\Macros\Predefined\Revenue;
use Traffic\Macros\Predefined\Sample;
use Traffic\Macros\Predefined\Status;
use Traffic\Macros\Predefined\Subid;
use Traffic\Macros\Predefined\Tid;
use Core\Application\Exception\Error;
use Traffic\Repository\AbstractBaseRepository;
use Traffic\Logging\Service\LoggerService;
use Component\Macros\Repository\CustomMacroRepository;
use Traffic\Macros\Predefined\CurrentDomain;
use Traffic\Macros\Predefined\TrafficSourceName;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Repository\ParameterRepository;
use Traffic\Service\SettingsService;

class MacroRepository extends AbstractBaseRepository
{
    private $_exclude = [
        'group_id', 'country_code', 'referer',
        'ua', 'example', 'keyword_cp1251',
        'keitaro_campaign_id',
        'keitaro_campaign_name', 'country_name', 'region_name',
        'se', 'useragent'
    ];

    private $_macroNames = [
        'click' => [],
        'conversion' => []
    ];

    private $_macros = [
        'click' => [],
        'conversion' => []
    ];

    private $_aliases = [];

    const DEFAULT_CUSTOM_PATH = '/application/macros';
    const CONVERSION = 'conversion';
    const CLICK = 'click';

    public function loadMacros()
    {
        if (!empty($this->_macros['click'])) {
            return;
        }

        $this->register('sample', new Sample());
        $this->register('random', new Random());
        $this->register('from_file', new FromFile());
        $this->register('date', new Date());
        $this->register('device_type', new DeviceType());
        $this->register('profit', new Profit());
        $this->register('revenue', new Revenue());
        $this->register('status', new Status());
        $this->register('original_status', new OriginalStatus());
        $this->register('tid', new Tid());
        $this->register('cost', new Cost());
        $this->register('conversion_cost', new ConversionCost());
        $this->register('conversion_revenue', new ConversionRevenue());
        $this->register('conversion_profit', new ConversionProfit());
        $this->register('campaign_name', new CampaignName());

        $this->alias('keitaro_campaign_id', 'campaign_id');
        $this->alias('keitaro_campaign_name', 'campaign_name');
        $this->alias('keitaro_landing_id', 'landing_id');
        $this->alias('keitaro_offer_id', 'offer_id');

        $this->register('operator', new Operator());
        $this->alias('carrier', 'operator');
        $this->register('connection_type', new ConnectionType());
        $this->register('city', new City());
        $this->alias('country_code', 'country');
        $this->register('country', new Country());
        $this->alias('country_name', 'country');
        $this->register('ip', new Ip());
        $this->register('region', new Region());
        $this->alias('region_name', 'region');
        $this->register('conversion_time', new ConversionTime());
        $this->register('debug', new Debug());

        $this->register('subid', new Subid());
        $this->alias('sub_id', 'subid');

        for ($i = 1; $i <= ParameterRepository::instance()->getSubIdCount(); $i++) {
            $param = 'sub_id_' . $i;
            $this->register($param, new AnyClickMacro($param));
        }

        for ($i = 1; $i <= ParameterRepository::instance()->getExtraParamCount(); $i++) {
            $param = 'extra_param_' . $i;
            $this->register($param, new AnyClickMacro($param));
        }

        $otherParams = ['se', 'source', 'ad_campaign_id', 'external_id', 'creative_id', 'referrer',
            'landing_id', 'ts_id', 'offer_id', 'campaign_id', 'stream_id',
            'isp', 'parent_campaign_id', 'is_bot', 'is_using_proxy', 'search_engine',
            'browser', 'browser_version', 'os', 'os_version', 'language', 'user_agent',
            'device_model', 'device_brand', 'destination', 'token'
        ];

        foreach ($otherParams as $param) {
            $this->register($param, new AnyClickMacro($param));
        }

        $otherParams = ['visitor_id'];

        foreach ($otherParams as $param) {
            $this->register($param, new AnyConversionMacro($param));
        }

        $this->register('keyword', new Keyword(Keyword::UTF8));
        $this->register('keyword_cp1251', new Keyword(Keyword::CP1251));
        $this->register('offer', new Offer());

        $this->register('current_domain', new CurrentDomain());
        $this->register('traffic_source_name', new TrafficSourceName());

        $this->alias('referer', 'referrer');
        $this->alias('se', 'search_engine');
        $this->alias('ua', 'user_agent');
        $this->alias('useragent', 'user_agent');

        if (empty($customPath)) {
            $customPath = ROOT . self::DEFAULT_CUSTOM_PATH;
        }

        $this->loadCustomMacros($customPath);
    }

    public function register($name, $obj)
    {
        $type = $this->_findType($obj);

        if (empty($type)) {
            return;
        }

        if (!empty($this->_macros[$type][$name])) {
            LoggerService::instance()->debug("Macro with name {$name} is already exists. Please use another name.");
            return;
        }
        $this->_macros[$type][$name] = $obj;
        $this->_macroNames[$type][] = $name;
    }

    public function alias($aliasName, $macroName)
    {
        if (!empty($this->_aliases[$aliasName])) {
            throw new Error("Macro alias {$aliasName} is defined");
        }

        $this->_aliases[$aliasName] = $macroName;
    }


    public function getMacroNames($type = null)
    {
        $this->loadMacros();
        if (empty($type)) {
            return array_flatten($this->_macroNames);
        }
        return $this->_macroNames[$type];
    }

    public function getActiveMacroNames($filter = null)
    {
        $this->loadMacros();
        $names = $this->getMacroNames($filter);
        asort($names);
        return array_values(array_diff($names, $this->_exclude));
    }

    /**
     * @return AbstractClickMacro[]|AbstractConversionMacro[]
     */
    public function getClickMacros()
    {
        return $this->getMacros(self::CLICK);
    }

    public function getConversionMacros()
    {
        return $this->getMacros(self::CONVERSION);
    }

    /**
     * @deprecated
     */
    public function getOnly($type)
    {
        $this->loadMacros();
        return $this->_macros[$type];
    }

    /**
     * @param $type
     * @return AbstractClickMacro[]|AbstractConversionMacro[]
     */
    public function getMacros($type)
    {
        $this->loadMacros();
        return $this->_macros[$type];
    }

    /**
     * @param $name
     * @return AbstractClickMacro|AbstractConversionMacro
     */
    public function getMacro($name)
    {
        $this->loadMacros();
        if (!empty($this->_aliases[$name])) {
            $name = $this->_aliases[$name];
        }
        foreach ($this->_macros as $type => $macros) {
            if (!empty($macros[$name])) {
                return $macros[$name];
            }
        }
    }

    public function isAlias($name)
    {
        return isset($this->_aliases[$name]);
    }

    public function getAliases($name)
    {
        $result = [];
        foreach ($this->_aliases as $alias => $macroName) {
            if ($macroName == $name) {
                $result[] = $alias;
            }
        }
        return $result;
    }

    public function loadCustomMacros($customPath)
    {
        if (!FeatureService::instance()->hasExtensionsFeature()) {
            return;
        }
        $customMacroArray = CustomMacroRepository::instance()->getCustomMacros($customPath, $this->_macroNames);
        foreach ($customMacroArray as $name => $macro) {
            $this->register($name, $macro);
        }
    }

    private function _findType($obj)
    {
        $type = BackCompatibility::getMacroType($obj);

        if (!empty($type)) {
            return $type;
        }

        switch (true) {
            case ($obj instanceof AbstractClickMacro):
                $type = self::CLICK;
                break;
            case ($obj instanceof AbstractConversionMacro):
                $type = self::CONVERSION;
                break;
            default:
                $name = get_class($obj);
                LoggerService::instance()->info("Macro '{$name}' is incorrect. It must be inherited of AbstractClickMacro or AbstractConversionMacro");
        }
        return $type;
    }
}