<?php
namespace Traffic\CachedData\DataGetter;

use Component\Domains\Repository\DomainsRepository;
use Component\Domains\Service\DomainService;
use Traffic\CachedData\Storage\StorageInterface;

class GetDomains implements DataGetterInterface
{
    const NAME = 'domains';
    const CACHE_KEY = 'DOMAINS';

    public function name()
    {
        return self::NAME;
    }

    public function get(StorageInterface $storage, $scope = null)
    {
        if (!empty($scope)) {
            throw new \Exception("scope is not implemented");
        }

        $result = $storage->get(self::CACHE_KEY);
        $domains = [];
        foreach ($result as $data) {
            $domains[] = DomainService::instance()->restore($data);
        }
        return $domains;
    }

    public function fallback($scope = null)
    {
        return DomainsRepository::instance()->all();
    }
}