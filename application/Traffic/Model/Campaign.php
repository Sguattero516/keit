<?php
namespace Traffic\Model;

use Component\Campaigns\Grid\CampaignGridDefinition;
use Component\Campaigns\Repository\CampaignRepository;
use Component\Campaigns\Serializer\CampaignSerializer;
use Component\Campaigns\Service\CampaignService;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;
use Traffic\Model\Mixin\StateMethodsTrait;
use Core\Entity\State;
use Component\Campaigns\Validator\CampaignValidator;
use Core\Model\AbstractModel;

class Campaign extends AbstractModel implements EntityModelInterface
{
    use StateMethodsTrait;

    const BIND_VISITOR_STREAM_LANDING_OFFER = 'slo';
    const BIND_VISITOR_STREAM = 's';
    const COST_TYPE_CPM = 'CPM';
    const COST_TYPE_CPC = 'CPC';
    const COST_TYPE_CPUC = 'CPUC';
    const COST_TYPE_REV_SHARE = 'RevShare';
    const COST_TYPE_CPA = 'CPA';
    const UNIQUENESS_METHOD_COOKIE_AND_IP = 'cookie_and_ip';
    const UNIQUENESS_METHOD_IP = 'ip';
    const BIND_VISITOR_STREAM_LANDING = 'sl';
    const COST_TYPE_CPS = 'CPS';
    const UNIQUENESS_METHOD_COOKIE = 'cookie';
    protected static $_tableName = 'campaigns';

    protected static $_cacheKey = 'CAMPAIGNS';

    protected static $_aclKey = 'campaigns';

    protected static $_entityName = 'campaign';

    protected static $_fields = [
        'id' => Type::INTEGER,
        'alias' => Type::STRING,
        'name' => Type::STRING,
        'type' => Type::STRING,
        'uniqueness_method' => Type::STRING,
        'uniqueness_use_cookies' => Type::BOOLEAN,
        'cookies_ttl' => Type::INTEGER,
        'mode' => Type::STRING,
        'position' => Type::INTEGER,
        'state' => Type::STRING,
        'updated_at' => Type::DATETIME,
        'cost_type' => Type::STRING,
        'cost_value' => Type::DECIMAL,
        'cost_currency' => Type::STRING,
        'group_id' => Type::INTEGER,
        'bind_visitors' => Type::STRING,
        'postbacks' => Type::VIRTUAL,
        'token' => Type::STRING,
        'notes' => Type::STRING,
        'traffic_source_id' => Type::INTEGER,
        'cost_auto' => Type::BOOLEAN,
        'domain_id' => Type::INTEGER,
        'parameters' => Type::JSON,
        'traffic_loss' => Type::DECIMAL
    ];

    const TYPE_POSITION = 'position';

    const TYPE_WEIGHT = 'weight';

    const UNIQUE_CHECK_BY_IP_UA = 'ip_ua';

    const UNIQUE_CHECK_BY_IP = 'ip';

    const DEFAULT_COOKIES_TTL = 24;
    const MIN_COOKIES_TTL = 1; //One hour
    const MAX_COOKIES_TTL = 8760; //One Year

    const MODE_BANNERS = 'banners';

    const MODE_TRACKING_PIXEL = 'tracking_pixel';

    const MODE_GENERAL = 'general';

    public static function validator()
    {
        return new CampaignValidator();
    }

    public function isCookiesForUniquenessUsing()
    {
        return !!$this->get('uniqueness_use_cookies');
    }

    public static function reportDefinition()
    {
        return new CampaignGridDefinition();
    }

    public static function repository()
    {
        return CampaignRepository::instance();
    }

    public static function service()
    {
        return CampaignService::instance();
    }

    public static function serializer()
    {
        return new CampaignSerializer(true);
    }

    public function getState()
    {
        return $this->get('state');
    }

    public function getPosition()
    {
        return $this->get('position');
    }

    public function getGroupId()
    {
        return $this->get('group_id');
    }

    public function isTypePosition()
    {
        return $this->get('type') === self::TYPE_POSITION;
    }

    public function isWeightPosition()
    {
        return $this->get('type') === self::TYPE_WEIGHT;
    }

    public function isDisabled()
    {
        return $this->get('state') === State::DISABLED;
    }

    public function isBindVisitorsEnabled()
    {
        $bind = $this->get('bind_visitors');
        return $this->isWeightPosition() && !empty($bind);
    }

    public function isBindVisitorsLandingEnabled()
    {
        $bind = $this->get('bind_visitors');
        return $this->isWeightPosition() && strlen($bind) >= 2;
    }

    public function isBindVisitorsOfferEnabled()
    {
        $bind = $this->get('bind_visitors');
        return $this->isWeightPosition() && strlen($bind) >= 3;
    }

    public function isCostAuto()
    {
        return $this->get('cost_auto');
    }

    public function getMode()
    {
        return $this->get('mode');
    }

    public function isBannerModeEnabled()
    {
        return $this->getMode() === self::MODE_BANNERS;
    }

    public function setActionPayload($value)
    {
        $this->set('action_payload', $value);
        return $this;
    }


    public function setPosition($value)
    {
        $this->set('position', $value);
        return $this;
    }

    public function getCampaignType()
    {
        return $this->get('type');
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function getUniquenessMethod()
    {
        return $this->get('uniqueness_method');
    }

    public function isUniqueByIpUa()
    {
        return $this->getUniquenessMethod() != self::UNIQUE_CHECK_BY_IP; // for legacy support
    }

    public function isUniqueByIp()
    {
        return $this->getUniquenessMethod() == self::UNIQUE_CHECK_BY_IP;
    }

    public function getCookiesTtl()
    {
        return (int) $this->get('cookies_ttl');
    }

    public function getActionType()
    {
        return $this->get('action_type');
    }

    public function getActionPayload()
    {
        return $this->get('action_payload');
    }

    public function getAlias()
    {
        return $this->get('alias');
    }

    public function getType()
    {
        return $this->get('type');
    }

    public function getCostType()
    {
        return $this->get('cost_type');
    }

    public function getCostValue()
    {
        return $this->get('cost_value');
    }

    public function getCostCurrency()
    {
        return $this->get('cost_currency');
    }

    public function getTrafficSourceId()
    {
        return $this->get('traffic_source_id');
    }

    public function getToken()
    {
        return $this->get('token');
    }

    public function isCostPerUnique()
    {
        return in_array($this->get('cost_type'), [self::COST_TYPE_CPUC, 'CPUV']);
    }

    public function isCostPerThousand()
    {
        return $this->get('cost_type') == self::COST_TYPE_CPM;
    }

    public function isCostPerAcquisition()
    {
        return $this->get('cost_type') == self::COST_TYPE_CPA;
    }

    public function isCostPerSale()
    {
        return $this->get('cost_type') == self::COST_TYPE_CPS;
    }

    public function isCostPerClick()
    {
        return in_array($this->get('cost_type'), [self::COST_TYPE_CPC, 'CPV']);
    }

    public function isCostRevShare()
    {
        return $this->get('cost_type') == self::COST_TYPE_REV_SHARE;
    }

    public function getParameters()
    {
        return $this->get('parameters');
    }

    public function getTrafficLoss()
    {
        return $this->get('traffic_loss');
    }
}