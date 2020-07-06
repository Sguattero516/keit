<?php
namespace Traffic\Repository;

use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\Model\AffiliateNetwork;

class CachedAffiliateNetworkRepository extends AbstractBaseRepository
{
    public function findCached($id)
    {
         return CachedDataRepository::instance()->get(AffiliateNetwork::entityName(), $id);
    }
}