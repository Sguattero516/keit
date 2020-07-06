<?php
namespace Traffic\Session;

use Core\Model\AbstractModel;
use Core\Type\Type;

class Session extends AbstractModel
{
    protected static $_tableName = 'sessions';

    protected static $_fields = [
        'id' => Type::INTEGER,
        'visitor_code' => Type::STRING,
        'expired_at' => Type::DATETIME,
        'payload' => Type::JSON
    ];
}