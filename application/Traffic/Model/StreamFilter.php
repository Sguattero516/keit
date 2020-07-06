<?php
namespace Traffic\Model;

use Component\StreamFilters\Repository\StreamFilterRepository;
use Component\StreamFilters\Service\StreamFilterService;
use Component\StreamFilters\Validator\StreamFilterValidator;
use Core\Entity\Model\EntityModelInterface;
use Core\Model\AbstractModel;
use Core\Type\Type;

class StreamFilter extends AbstractModel implements EntityModelInterface
{
    const REJECT = 'reject';
    const ACCEPT = 'accept';

    protected static $_tableName = 'stream_filters';

    protected static $_entityName = 'stream_filters';

    protected static $_fields = array(
        'id' => Type::INTEGER,
        'stream_id' => Type::INTEGER,
        'name' => Type::STRING,
        'mode' => Type::STRING,
        'payload' => Type::JSON,

        'oid' => Type::VIRTUAL
    );

    private $_cachedStream;

    public static function validator()
    {
        return new StreamFilterValidator();
    }

    public static function repository()
    {
        return StreamFilterRepository::instance();
    }

    public static function service()
    {
        return StreamFilterService::instance();
    }

    public function setStream(BaseStream $stream)
    {
        $this->_cachedStream = $stream;
        $this->_data['stream_id'] = $stream->getId();
        return $this;
    }

    public function getStreamId()
    {
        return $this->get('stream_id');
    }

    public function getPayload()
    {
        return $this->get('payload');
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function setStreamId($id)
    {
        $this->set('stream_id', $id);
        return $this;
    }

    public function getMode()
    {
        return $this->get('mode');
    }
}