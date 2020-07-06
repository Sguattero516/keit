<?php
namespace Traffic\Model;

use Component\Offers\Service\StreamOfferAssociationService;
use Component\Offers\Validator\StreamOfferAssociationValidator;
use Core\Model\AbstractModel;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;
use Component\Streams\Repository\StreamOfferAssociationRepository;

class StreamOfferAssociation extends AbstractModel implements EntityModelInterface
{
    protected static $_tableName = 'stream_offer_associations';

    protected static $_entityName = 'offer_stream_association';

    protected static $_fields = [
        'id' => Type::INTEGER,
        'stream_id' => Type::INTEGER,
        'offer_id'  => Type::INTEGER,
        'state' => Type::STRING,
        'share' => Type::INTEGER,
        'created_at' => Type::DATETIME,
        'updated_at' => Type::DATETIME
    ];

    public static function validator()
    {
        return new StreamOfferAssociationValidator();
    }

    public static function repository()
    {
        return StreamOfferAssociationRepository::instance();
    }

    public static function service()
    {
        return StreamOfferAssociationService::instance();
    }

    public function getShare()
    {
        return $this->get('share');
    }

    public function getState()
    {
        return $this->get('state');
    }

    public function getOfferId()
    {
        return $this->get('offer_id');
    }
}