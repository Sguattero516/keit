<?php
namespace Traffic\HitLimit\Storage;

use Traffic\HitLimit\Repository\HitRequestRepository;
use Traffic\Model\StreamFilter;
use Core\Db\DataRepository;
use Core\Db\Db;
use Core\Model\AbstractModel;
use Traffic\HitLimit\Service\HitRequestService;
use Traffic\Model\BaseStream;
use Traffic\HitLimit\Model\HitRequest;
use Component\StreamFilters\Repository\StreamFilterRepository;

class DbStorage implements StorageInterface
{
    const FILTER_NAME = 'limit';

    public function store(BaseStream $stream, \DateTime $currentDateTime)
    {
        HitRequestService::instance()->create([
            'stream_id' => $stream->getId(),
            'datetime' => $currentDateTime->format(AbstractModel::DATETIME_FORMAT)
        ]);
    }

    public function perHour(BaseStream $stream, \DateTime $currentDateTime)
    {
        $where = [];
        $where[] = 'stream_id = ' . Db::quote($stream->getId());
        $where[] = 'datetime >= DATE_SUB(' .
            Db::quote($currentDateTime->format(AbstractModel::DATETIME_FORMAT))
            . ', INTERVAL 1 HOUR)';
        return HitRequestRepository::instance()->count(implode(' AND ', $where));
    }

    public function perDay(BaseStream $stream, \DateTime $date)
    {
        $where = [];
        $where[] = 'stream_id = ' . Db::quote($stream->getId());
        $where[] = 'datetime >= DATE_SUB(' .
            Db::quote($date->format(AbstractModel::DATETIME_FORMAT))
            . ', INTERVAL 1 DAY)';
        return HitRequestRepository::instance()->count(implode(' AND ', $where));
    }

    public function total(BaseStream $stream)
    {
        return HitRequestRepository::instance()->count('stream_id = ' . Db::quote($stream->getId()));
    }

    public function prune(\DateTime $currentDateTime)
    {
        $exceptions = $this->_getStreamIdsWithLimitTotal();

        $where = 'datetime < DATE_SUB(' . Db::quote($currentDateTime->format(AbstractModel::DATETIME_FORMAT)). ', INTERVAL 1 DAY)';

        if (count($exceptions)) {
            $where .= ' AND stream_id NOT IN (' . implode(',', $exceptions) . ')';
        }

        HitRequestService::instance()->directDeleteAll($where);
    }

    private function _getStreamIdsWithLimitTotal()
    {
        $where = 'name = ' . Db::quote(self::FILTER_NAME);
        $ids = [];
        /**
         * @var $items StreamFilter[]
         */
        $items = StreamFilterRepository::instance()->all($where);
        foreach ($items as $filter) {
            $payload = $filter->getPayload();
            if (!empty($payload['total'])) {
                $ids[] = $filter->getStreamId();
            }
        }
        return $ids;
    }
}
