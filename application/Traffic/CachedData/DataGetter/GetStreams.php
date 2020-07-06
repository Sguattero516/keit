<?php
namespace Traffic\CachedData\DataGetter;

use Component\Streams\Repository\StreamRepository;
use Component\Streams\Service\StreamService;
use Traffic\CachedData\Storage\StorageInterface;
use Core\Db\Db;
use Core\Entity\State;

class GetStreams implements DataGetterInterface
{
    const NAME = 'streams';
    const CACHE_KEY = 'STRMS';

    public function name()
    {
        return self::NAME;
    }

    public function get(StorageInterface $storage, $scope = null)
    {
        if (empty($scope['campaign_id'])) {
            throw new \Exception("No 'campaign_id' in scope");
        }
        $key = static::cacheKey($scope['campaign_id']);
        $groupedRows = $storage->get($key);

        return $this->_groupedRowsToEntities($groupedRows);
    }

    public function fallback($scope = null)
    {
        $campaignId = $scope['campaign_id'];
        $groupedRows = static::getGroupedStreams([$campaignId]);
        if (empty($groupedRows)) {
            return $groupedRows;
        }
        return $this->_groupedRowsToEntities($groupedRows[$campaignId]);
    }

    public function _groupedRowsToEntities($groupedRows)
    {
        $result = [];
        foreach ($groupedRows as $type => $rows) {
            $subResult = [];
            foreach ($rows as $key => $row) {
                $subResult[] = StreamService::instance()->restore($row);
            }
            $result[$type] = $subResult;
        }
        return $result;
    }

    public static function getGroupedStreams($campaignIds)
    {
        $where = "campaign_id IN (" . implode(',', Db::quote($campaignIds)) . ")";
        $where .= ' AND state = ' . Db::quote(State::ACTIVE);
        $streams = StreamRepository::instance()->rawRows('*', $where, 'position');

        $groupedStreams = [];

        foreach ($streams as $stream) {
            $campaignId = $stream['campaign_id'];
            $type = $stream['type'];
            if (empty($groupedStreams[$campaignId][$type])) {
                $groupedStreams[$campaignId][$type] = [];
            }
            $groupedStreams[$campaignId][$type][] = $stream;
        }
        return $groupedStreams;
    }


    public static function cacheKey($campaignId)
    {
        return implode('_', [self::CACHE_KEY, $campaignId]);
    }
}