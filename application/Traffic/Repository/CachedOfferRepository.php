<?php
namespace Traffic\Repository;

use Core\Entity\Model\EntityModelInterface;
use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\Cache\NoCache;
use Traffic\Model\Offer;

class CachedOfferRepository extends AbstractBaseRepository
{
    /**
     * @param $id int
     * @return EntityModelInterface
     * @throws NoCache
     */
    public function findCached($id)
    {
         return CachedDataRepository::instance()->get(Offer::entityName(), $id);
    }
}