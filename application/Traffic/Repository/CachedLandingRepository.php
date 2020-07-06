<?php
namespace Traffic\Repository;

use Traffic\CachedData\DataGetter\GetLandingsUrls;
use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\Model\Landing;

class CachedLandingRepository extends AbstractBaseRepository
{
    public function findCached($id)
    {
        return CachedDataRepository::instance()->get(Landing::entityName(), $id);
    }

    public function urls()
    {
        return CachedDataRepository::instance()->get(GetLandingsUrls::NAME);
    }
}