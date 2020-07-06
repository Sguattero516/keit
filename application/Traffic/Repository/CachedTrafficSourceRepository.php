<?php
namespace Traffic\Repository;

use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\Cache\NoCache;
use Traffic\Model\TrafficSource;

class CachedTrafficSourceRepository extends AbstractBaseRepository
{
    /**
     * @param $id int
     * @return \Core\Entity\Model\EntityModelInterface|null
     * @throws NoCache
     */
    public function findCached($id)
    {
        return CachedDataRepository::instance()->get(TrafficSource::entityName(), $id);
    }
}