<?php
namespace Traffic\Repository;

use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\Cache\NoCache;
use Traffic\Model\BaseStream;
use Traffic\Model\StreamLandingAssociation;

class CachedStreamLandingAssociationRepository extends AbstractBaseRepository
{

    /**
     * @param BaseStream $stream
     * @return StreamLandingAssociation[]
     * @throws NoCache
     */
    public function getCachedByStream(BaseStream $stream)
    {
         return CachedDataRepository::instance()->get(StreamLandingAssociation::entityName(), ['stream_id' => $stream->getId()]);
    }
}