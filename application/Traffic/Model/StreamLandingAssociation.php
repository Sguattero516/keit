<?php
namespace Traffic\Model;

use Component\Landings\Service\StreamLandingAssociationService;
use Component\Landings\Validator\StreamLandingAssociationValidator;
use Core\Model\AbstractModel;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;
use Component\Streams\Repository\StreamLandingAssociationRepository;

class StreamLandingAssociation extends AbstractModel implements EntityModelInterface
{
    protected static $_tableName = 'stream_landing_associations';

    protected static $_entityName = 'landing_stream_association';

    protected static $_fields = [
        'id' => Type::INTEGER,
        'stream_id' => Type::INTEGER,
        'landing_id'  => Type::INTEGER,
        'state' => Type::STRING,
        'share' => Type::INTEGER,
        'created_at' => Type::DATETIME,
        'updated_at' => Type::DATETIME
    ];

    public static function validator()
    {
        return new StreamLandingAssociationValidator();
    }

    public static function repository()
    {
        return StreamLandingAssociationRepository::instance();
    }

    public static function service()
    {
        return StreamLandingAssociationService::instance();
    }

    public function getShare()
    {
        return $this->get('share');
    }

    public function getState()
    {
        return $this->get('state');
    }

    public function getLandingId()
    {
        return $this->get('landing_id');
    }
}