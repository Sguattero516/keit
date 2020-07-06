<?php
namespace Traffic\Model;

use Component\Landings\Grid\LandingGridDefinition;
use Component\Landings\Repository\LandingRepository;
use Component\Landings\Serializer\LandingSerializer;
use Component\Landings\Service\LandingService;
use Component\Landings\Validator\LandingValidator;
use Core\Model\AbstractModel;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;
use Traffic\Model\Mixin\StateMethodsTrait;
use Traffic\Model\Mixin\StreamActionableMethodsTrait;

class Landing extends AbstractModel implements EntityModelInterface
{
    use StateMethodsTrait;
    use StreamActionableMethodsTrait;

    protected static $_className = 'Landing';

    protected static $_tableName = 'landings';

    protected static $_cacheKey = 'LANDING';

    protected static $_aclKey = 'landings';

    protected static $_entityName = 'landing';

    protected static $_fields = [
        'id' => Type::INTEGER,
        'landing_type' => Type::STRING,
        'action_type' => Type::STRING,
        'action_payload' => Type::STRING,
        'action_options' => Type::JSON,
        'name' => Type::STRING,
        'group_id' => Type::INTEGER,
        'offer_count' => Type::INTEGER,
        'notes' => Type::STRING,
        'state' => Type::STRING,
        'created_at' => Type::DATETIME,
        'updated_at' => Type::DATETIME,
        'archive' => Type::VIRTUAL,
        'local_path' => Type::VIRTUAL,
        'preview_path' => Type::VIRTUAL,
        'group' => Type::VIRTUAL,
    ];

    public static function serializer()
    {
        return new LandingSerializer(true);
    }

    public static function validator()
    {
        return new LandingValidator();
    }

    public static function reportDefinition()
    {
        return new LandingGridDefinition();
    }

    public static function service()
    {
        return LandingService::instance();
    }

    public static function repository()
    {
        return LandingRepository::instance();
    }

}