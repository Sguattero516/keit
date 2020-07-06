<?php
namespace Traffic\Model;

use Component\TrafficSources\Grid\TrafficSourceGridDefinition;
use Component\TrafficSources\Serializer\TrafficSourceSerializer;
use Component\TrafficSources\Service\TrafficSourceService;
use Core\Model\AbstractModel;
use Component\TrafficSources\Validator\TrafficSourceValidator;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;
use Component\TrafficSources\Repository\TrafficSourceRepository;

class TrafficSource extends AbstractModel implements  EntityModelInterface
{
    protected static $_className = 'TrafficSource';

    protected static $_tableName = 'traffic_sources';

    protected static $_cacheKey = 'SOURCE';

    protected static $_aclKey = 'traffic_sources';

    protected static $_entityName = 'ts';

    protected static $_fields = array(
        'id' => Type::INTEGER,
        'name' => Type::STRING,
        'postback_url' => Type::STRING,
        'postback_statuses' => Type::JSON,
        'template_name' => Type::STRING,
        'accept_parameters' => Type::BOOLEAN,
        'parameters' => Type::JSON,
        'notes' => Type::STRING,
        'state' => Type::STRING,
        'created_at' => Type::DATETIME,
        'updated_at' => Type::DATETIME,
        'traffic_loss' => Type::DECIMAL,
        'update_in_campaigns' => Type::VIRTUAL,
    );

    public static function serializer()
    {
        return new TrafficSourceSerializer();
    }

    public static function repository()
    {
        return TrafficSourceRepository::instance();
    }

    public static function service()
    {
        return TrafficSourceService::instance();
    }

    public static function reportDefinition()
    {
        return new TrafficSourceGridDefinition();
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function getPostback()
    {
        return $this->get('postback_url');
    }

    public function getParameters()
    {
        return $this->get('parameters');
    }

    public function getTrafficLoss()
    {
        return $this->get('traffic_loss');
    }

    public function getPostbackStatuses()
    {
        return $this->get('postback_statuses');
    }

    public static function validator()
    {
        return new TrafficSourceValidator();
    }
}