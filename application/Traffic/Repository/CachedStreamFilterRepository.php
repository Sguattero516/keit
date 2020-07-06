<?php
namespace Traffic\Repository;

use Traffic\Model\StreamFilter;
use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\CachedData\DataGetter\GetStreamFilters;
use Core\Entity\Model\EntityModelInterface;
use Traffic\Cache\NoCache;
use Traffic\Model\BaseStream;

class CachedStreamFilterRepository extends AbstractBaseRepository
{

    /**
     * @param BaseStream $stream
     * @return StreamFilter[]|EntityModelInterface
     * @throws NoCache
     */
    public function allCached(BaseStream $stream)
    {
         return CachedDataRepository::instance()->get(GetStreamFilters::NAME, ['stream_id' => $stream->getId()]);
    }
}