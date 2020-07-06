<?php
namespace Traffic\HitLimit\Model;

use Core\Model\AbstractModel;
use Core\Type\Type;
use Core\Entity\Model\EntityModelInterface;

class HitRequest extends AbstractModel implements EntityModelInterface
{
    protected static $_tableName = 'requests';

    protected static $_fields = [
        'id' => Type::INTEGER,
        'stream_id' => Type::INTEGER,
        'datetime' => Type::DATETIME
    ];
}