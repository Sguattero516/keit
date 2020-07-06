<?php
namespace Traffic\Model;

use Component\Streams\Repository\StreamRepository;
use Component\Streams\Service\StreamService;
use Component\Streams\StreamValidator;
use Core\Application\Exception\Error;
use Core\Model\AbstractModel;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;
use Traffic\Model\Mixin\StateMethodsTrait;

class BaseStream extends AbstractModel implements EntityModelInterface
{
    use StateMethodsTrait;

    const ACTION = 'action';
    const LANDINGS = 'landings';
    const OFFERS = 'offers';
    const REDIRECT = 'redirect';

    protected static $_tableName = 'streams';

    protected static $_cacheKey = 'STREAM';

    protected static $_aclKey;

    protected static $_entityName = 'stream';

    protected static $_fields = array(
        'id' => Type::INTEGER,
        'name' => Type::STRING,
        'schema' => Type::STRING,
        'type' => Type::STRING,
        'campaign_id' => Type::INTEGER,
        'position' => Type::INTEGER,
        // @deprecated - weight is controlled by position
        'chance' => Type::INTEGER,
        'action_type' => Type::STRING,
        'action_payload' => Type::STRING,
        'action_options' => Type::JSON,
        'comments' => Type::STRING,
        'state' => Type::STRING,
        'updated_at' => Type::DATETIME,
        'filters' => Type::VIRTUAL,
        'triggers' => Type::VIRTUAL,
        'landings' => Type::VIRTUAL,
        'offers' => Type::VIRTUAL,
        'collect_clicks' => Type::BOOLEAN,
        'filter_or' => Type::BOOLEAN
    );

    const DEFAULT_SCHEMA = 'redirect';

    public static function validator()
    {
        return new StreamValidator();
    }

    public static function repository()
    {
        return StreamRepository::instance();
    }

    public static function service()
    {
        return StreamService::instance();
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function getType()
    {
        return $this->get('type');
    }

    public function setPosition($value)
    {
        $this->set('position', (int)$value);
        return $this;
    }

    public function getPosition()
    {
        return $this->get('position');
    }

    public function getUpdatedAt()
    {
        return $this->get('updated_at');
    }

    public function setUrl($value)
    {
        $this->set('action_payload', $value);
        return $this;
    }

    public function setState($value)
    {
        $this->set('state', $value);
        return $this;
    }

    public function setCampaignId($value)
    {
        $this->set('campaign_id', (int) $value);
        return $this;
    }

    public function getComments()
    {
        return $this->get('comments');
    }

    public function getCampaignId()
    {
        return $this->get('campaign_id');
    }

    public function getCampaign()
    {
        throw new Error('getCampaign deprecated');
    }

    public function getActionOptions()
    {
        return $this->get('action_options');
    }

    public function getActionOption($name)
    {
        $options = $this->getActionOptions();
        if (isset($options[$name])) {
            return $options[$name];
        }
        return null;
    }

    public function setActionOptions($actionOptions)
    {
        return $this->set('action_options', $actionOptions);
    }

    public function getActionType()
    {
        return $this->get('action_type');
    }

    public function setActionType($actionType)
    {
        return $this->set('action_type', $actionType);
    }

    public function setActionPayload($value)
    {
        return $this->set('action_payload', $value);
    }

    public function getActionPayload()
    {
        return $this->get('action_payload');
    }

    public function getWeight()
    {
        return $this->get('position');
    }


    // @deprecated - weight is controlled by position
    public function setChance($value)
    {
        $this->set('chance', $value);
        return $this;
    }

    // @deprecated - weight is controlled by position
    public function getChance()
    {
        return $this->get('chance');
    }

    public function isFilterOr()
    {
        return $this->get('filter_or');
    }

    public function getSchema()
    {
        if (!$this->get('schema')) {
            return self::DEFAULT_SCHEMA;
        }
        return $this->get('schema');
    }
}