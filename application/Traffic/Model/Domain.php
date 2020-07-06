<?php
namespace Traffic\Model;

use Component\Domains\Repository\DomainsRepository;
use Component\Domains\Serializer\DomainSerializer;
use Component\Domains\Service\DomainService;
use Component\Domains\Validator\DomainValidator;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;
use Core\Model\AbstractModel;

class Domain extends AbstractModel implements EntityModelInterface
{
    const NETWORK_STATUS_ACTIVE = 'active';
    const NETWORK_STATUS_ERROR = 'error';

    protected static $_className = 'Domain';

    protected static $_tableName = 'domains';

    protected static $_aclKey = 'domains';

    protected static $_entityName = 'domain';

    protected static $_fields = [
        'id' => Type::INTEGER,
        'name' => Type::STRING,
        'is_ssl' => Type::BOOLEAN,
        'network_status' => Type::STRING,
        'error_description' => Type::STRING,
        'default_campaign_id' => Type::INTEGER,
        'wildcard' => Type::BOOLEAN,
        'state' => Type::STRING,
        'catch_not_found' => Type::BOOLEAN,
        'notes' => Type::STRING,
        'created_at' => Type::DATETIME,
        'updated_at' => Type::DATETIME
    ];

    public static function serializer()
    {
        return new DomainSerializer();
    }

    public static function service()
    {
        return DomainService::instance();
    }

    public static function validator()
    {
        return new DomainValidator();
    }

    public static function repository()
    {
        return DomainsRepository::instance();
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function isSSL()
    {
        return $this->get('is_ssl');
    }

    public function isWildcard()
    {
        return $this->get('wildcard');
    }

    public function getDefaultCampaignId()
    {
        return $this->get('default_campaign_id');
    }
}