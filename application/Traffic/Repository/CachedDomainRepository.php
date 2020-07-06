<?php
namespace Traffic\Repository;

use Core\Entity\Model\EntityModelInterface;
use GuzzleHttp\Psr7\Uri;
use Traffic\Cache\NoCache;
use Traffic\CachedData\Repository\CachedDataRepository;
use Traffic\CachedData\DataGetter\GetDomains;
use Traffic\Model\Domain;

class CachedDomainRepository extends AbstractBaseRepository
{

    /**
     * @return null|Domain[]|EntityModelInterface[]
     * @throws NoCache
     */
    public function allActiveCached()
    {
        try {
            return CachedDataRepository::instance()->get(GetDomains::NAME);
        } catch (NoCache $e) {
            // в CachedDataRepository уже запланирован warmup на этот случай, сейчас просто не падаем (KTDS-2822)
            return [];
        }
    }

    public function getCampaignIdByUrl(Uri $uri)
    {
        /**
         * @var $domains Domain[]
         */
        $domains = $this->allActiveCached();
        $requestedDomain = $uri->getHost();
        $pathExists = ($uri->getPath() !== '/' && $uri->getPath() !== '');
        foreach ($domains as $domain) {
            $campaign = null;
            if ($domain->getName() === $requestedDomain) {
                $campaign  = $domain->getDefaultCampaignId();
            }

            if (!$campaign && $domain->isWildcard()) {
                $campaign = $this->_tryFindAsSubdomain($domain, $requestedDomain);
            }

            if ($campaign && (!$pathExists || $domain->get('catch_not_found'))) {
                return $campaign;
            }
        }
        return null;
    }

    private function _tryFindAsSubdomain(Domain $domain, $requestedDomain)
    {
        $requestedDomainParts = isset($requestedDomain) ? explode('.', $requestedDomain) : null;

        if (!empty($requestedDomainParts)) {
            $domainHostParts = explode('.', $domain->getName());
            $similar = true;
            // FIXME: код переусложнен, надо переписать
            for (
                $i = count($domainHostParts) - 1, $j = count($requestedDomainParts) - 1;
                $i >= 0;
                $i--, $j--
            ) {
                if ($domainHostParts[$i] != $requestedDomainParts[$j]) {
                    $similar = false;
                    break;
                }
            }
            if ($similar) {
                return $domain->getDefaultCampaignId();
            }
        }
        return null;
    }
}
