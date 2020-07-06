<?php
namespace Traffic\Repository;

use Core\Entity\Model\EntityModelInterface;
use Traffic\Cache\NoCache;
use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\Model\BaseStream;
use Traffic\Model\StreamOfferAssociation;

class CachedStreamOfferAssociationRepository extends AbstractBaseRepository
{
    /**
     * @param BaseStream $stream
     * @param bool $useFallbackStrategy
     * @return StreamOfferAssociation[]|EntityModelInterface[]
     * @throws NoCache
     */
    public function getCachedByStream(BaseStream $stream, $useFallbackStrategy = true)
    {
         return CachedDataRepository::instance()->get(StreamOfferAssociation::entityName(), [
             'stream_id' => $stream->getId()
         ], $useFallbackStrategy);
    }

    public function cachedStreamHasOfferId(BaseStream $stream, $offerId)
    {
        $offerAssocs = $this->getCachedByStream($stream, true);
        $found = false;
        foreach ($offerAssocs as $offerAssoc) {
            if ($offerAssoc->getOfferId() == $offerId) {
                $found = true;
                break;
            }
        }
        return $found;
    }


    public function hasCachedOffers(BaseStream $stream)
    {
        return CachedDataRepository::instance()->exists(StreamOfferAssociation::entityName(), [
            'stream_id' => $stream->getId()
        ]);
    }
}