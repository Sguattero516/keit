<?php
namespace Traffic\Model;

use Component\AffiliateNetworks\Grid\AffiliateNetworkGridDefinition;
use Component\AffiliateNetworks\Serializer\AffiliateNetworkSerializer;
use Component\AffiliateNetworks\Service\AffiliateNetworkService;
use Component\AffiliateNetworks\Validator\AffiliateNetworkValidator;
use Core\Model\AbstractModel;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;
use Component\AffiliateNetworks\Repository\AffiliateNetworksRepository;

class AffiliateNetwork extends AbstractModel implements EntityModelInterface
{
    const PULL_API_TIMEOUT_KEY ="time_to_query";
    const PULL_API_URL_KEY ="url";
    const PULL_API_FORMAT_KEY ="format";
    const PULL_API_MACRO_KEY ="required_macro";

    protected static $_className = 'AffiliateNetwork';

    protected static $_tableName = 'affiliate_networks';

    protected static $_aclKey = 'affiliate_networks';

    protected static $_cacheKey = 'AFFILIATE_NETWORK';

    protected static $_entityName = 'affiliate_network';

    protected static $_fields = array(
        'id' => Type::INTEGER,
        'name' => Type::STRING,
        'postback_url' => Type::STRING,
        'offer_param' => Type::STRING,
        'state' => Type::STRING,
        'template_name' => Type::STRING,
        'notes' => Type::STRING,
        'created_at' => Type::DATETIME,
        'updated_at' => Type::DATETIME
    );

    public static function serializer()
    {
        return new AffiliateNetworkSerializer(true);
    }

    public static function validator()
    {
        return new AffiliateNetworkValidator();
    }

    public static function reportDefinition()
    {
        return new AffiliateNetworkGridDefinition();
    }

    public static function repository()
    {
        return AffiliateNetworksRepository::instance();
    }

    public static function service()
    {
        return AffiliateNetworkService::instance();
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function getOfferParam()
    {
        return $this->get('offer_param');
    }

    public function getPullApiMacros()
    {
        $params = $this->getPullApiOptions();
        if (empty($params)) {
            return null;
        }
        return $params['required_macro'];
    }

    public function getPostbackUrl()
    {
        return $this->get("postback_url");
    }
}
