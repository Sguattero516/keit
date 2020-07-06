<?php
namespace Traffic\Model;

use Component\Offers\Grid\OfferGridDefinition;
use Component\Offers\Serializer\OfferSerializer;
use Component\Offers\Service\OfferService;
use Component\Offers\Validator\OfferValidator;
use Core\Type\Type;
use Traffic\Model\Mixin\StreamActionableMethodsTrait;
use Core\Model\AbstractModel;
use Core\Entity\Model\EntityModelInterface;
use Traffic\Model\Mixin\StateMethodsTrait;
use Component\Offers\Repository\OfferRepository;

class Offer extends AbstractModel implements EntityModelInterface
{
    use StateMethodsTrait;
    use StreamActionableMethodsTrait;

    const PAYOUT_TYPE_CPC = 'CPC';
    const PAYOUT_TYPE_CPA = 'CPA';
    protected static $_className = 'Offer';

    protected static $_tableName = 'offers';

    protected static $_cacheKey = 'OFFER';

    protected static $_aclKey = 'offers';

    protected static $_entityName = 'offer';

    protected static $_fields = [
        'id' => Type::INTEGER,
        'name' => Type::STRING,
        'group_id' => Type::INTEGER,
        'action_payload' => Type::STRING,
        'country' => Type::JSON,
        'affiliate_network_id' => Type::INTEGER,
        'payout_value' => Type::DECIMAL,
        'payout_currency' => Type::STRING,
        'payout_type' => Type::STRING,
        'notes' => Type::STRING,
        'state' => Type::STRING,
        'created_at' => Type::DATETIME,
        'updated_at' => Type::DATETIME,
        'payout_auto' => Type::BOOLEAN,
        'payout_upsell' => Type::BOOLEAN,
        'offer_type' => Type::STRING,
        'action_type' => Type::STRING,
        'action_options' => Type::JSON,

        'conversion_cap_enabled' => Type::BOOLEAN,
        'daily_cap' => Type::INTEGER,
        'conversion_timezone' => Type::STRING,
        'alternative_offer_id' => Type::INTEGER,

        'archive' => Type::VIRTUAL,
        'local_path' => Type::VIRTUAL,
        'preview_path' => Type::VIRTUAL,
        'group' => Type::VIRTUAL,
        'affiliate_network' => Type::VIRTUAL,
    ];

    public static function validator()
    {
        return new OfferValidator();
    }

    public static function serializer()
    {
        return new OfferSerializer(true);
    }

    public static function repository()
    {
        return OfferRepository::instance();
    }

    public static function service()
    {
        return OfferService::instance();
    }

    public static function reportDefinition()
    {
        return new OfferGridDefinition();
    }

    public function getPayoutValue()
    {
        return $this->get('payout_value');
    }

    public function getPayoutCurrency()
    {
        return $this->get('payout_currency');
    }

    public function isPayoutAuto()
    {
        return $this->get('payout_auto');
    }

    public function isCPC()
    {
        return $this->get('payout_type') == self::PAYOUT_TYPE_CPC;
    }

    public function getAffiliateNetworkId()
    {
        return $this->get('affiliate_network_id');
    }

    public function isConversionCapEnabled()
    {
        return $this->get('conversion_cap_enabled');
    }

    public function getDailyCap()
    {
        return $this->get('daily_cap');
    }
}