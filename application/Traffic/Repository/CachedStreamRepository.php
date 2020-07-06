<?php
namespace Traffic\Repository;

use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\CachedData\DataGetter\GetStream;
use Traffic\CachedData\DataGetter\GetStreams;
use Traffic\Cache\NoCache;
use Traffic\Model\Campaign;
use Traffic\Model\Stream;
use Traffic\Model\StreamCollection;

class CachedStreamRepository extends AbstractBaseRepository
{
    /**
     * @param Campaign $campaign
     * @return StreamCollection
     * @throws NoCache
     */
    public function getCachedActiveStreams(Campaign $campaign)
    {
        $result = CachedDataRepository::instance()->get(GetStreams::NAME, [
            'campaign_id' => $campaign->getId()
        ]);
        return new StreamCollection($result);
    }

    /**
     * @param $id
     * @return Stream|null
     * @throws NoCache
     */
    public function findCachedStream($id)
    {
         return CachedDataRepository::instance()->get(GetStream::NAME, $id);
    }

}