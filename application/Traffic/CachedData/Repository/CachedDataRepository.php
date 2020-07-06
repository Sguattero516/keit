<?php
namespace Traffic\CachedData\Repository;

use Core\Db\Db;
use Core\Entity\Model\EntityModelInterface;
use Core\EntityEventManager\EventHandler\EventHandlerInterface;
use Core\EntityEventManager\Service\EntityEventService;
use Traffic\Cache\NoCache;
use Traffic\CachedData\DataGetter\DataGetterInterface;
use Traffic\CachedData\DataGetter\GetCampaign;
use Traffic\CachedData\DataGetter\GetCampaignAliases;
use Traffic\CachedData\DataGetter\GetCampaignTokens;
use Traffic\CachedData\DataGetter\GetDomains;
use Traffic\CachedData\DataGetter\GetLandingsUrls;
use Traffic\CachedData\DataGetter\GetResource;
use Traffic\CachedData\DataGetter\GetStream;
use Traffic\CachedData\DataGetter\GetStreamAssociations;
use Traffic\CachedData\DataGetter\GetStreamFilters;
use Traffic\CachedData\DataGetter\GetStreams;
use Traffic\CachedData\Storage\NullStorage;
use Traffic\CachedData\Storage\StorageInterface;
use Traffic\CachedData\WarmupScheduler;
use Traffic\Logging\Service\LoggerService;
use Traffic\Model\AffiliateNetwork;
use Traffic\Model\Landing;
use Traffic\Model\Offer;
use Traffic\Model\StreamLandingAssociation;
use Traffic\Model\StreamOfferAssociation;
use Traffic\Model\TrafficSource;
use Traffic\Repository\AbstractBaseRepository;
use Traffic\Tools\Tools;

class CachedDataRepository extends AbstractBaseRepository
{
    /**
     * @var DataGetterInterface[]
     */
    private $_dataGetters = [];
    /**
     * @var StorageInterface
     */
    private $_storage;

    public function __construct()
    {
        $this->register(new GetCampaignAliases());
        $this->register(new GetCampaignTokens());
        $this->register(new GetCampaign());
        $this->register(new GetStreams());
        $this->register(new GetStream());
        $this->register(new GetResource(Landing::entityName()));
        $this->register(new GetResource(Offer::entityName()));
        $this->register(new GetResource(TrafficSource::entityName()));
        $this->register(new GetResource(AffiliateNetwork::entityName()));
        $this->register(new GetDomains());
        $this->register(new GetStreamAssociations(StreamLandingAssociation::entityName()));
        $this->register(new GetStreamAssociations(StreamOfferAssociation::entityName()));
        $this->register(new GetStreamFilters());
        $this->register(new GetLandingsUrls());
    }

    public function warmup()
    {
        LoggerService::instance()->debug('[CachedDataRepository] warmup');
        $warmedUp = [];
        foreach (EntityEventService::instance()->getSubscriptions() as $entityName => $handlers) {
            /**
             * @var $handlers EventHandlerInterface[]
             */
            foreach ($handlers as $handler) {
                if (!in_array($handler, $warmedUp)) {
                    $handler->warmup($this->getStorage());
                    $warmedUp[] = $handler;
                }
            }
        }
        $this->getStorage()->commit();
    }

    public function setStorage(StorageInterface $storage)
    {
        $this->_storage = $storage;
        return $this;
    }

    public function getStorage()
    {
        return $this->_storage;
    }

    public function register(DataGetterInterface $getter)
    {
        $dataName = $getter->name();
        if (!empty($this->_dataGetters[$dataName])) {
            throw new \Exception("Data name {$dataName} already have a getter " . get_class($this->_dataGetters[$dataName]));
        }
        $this->_dataGetters[$dataName] = $getter;
    }

    /**
     * @param string $getterName
     * @param null|string $scope
     * @param boolean $useFallbackStrategy Если true, запустить выполнить fallback strategy
     *                                     и запланировать обновление кэша
     * @return EntityModelInterface|EntityModelInterface[]|null
     * @throws \Exception|NoCache
     */
    public function get($getterName, $scope = null, $useFallbackStrategy = true)
    {
        if (empty($this->getStorage())) {
            throw new \Exception('No storage is set');
        }

        $getter = $this->getGetter($getterName);
        try {
            return $getter->get($this->getStorage(), $scope);
        } catch (NoCache $e) {
            if (!$useFallbackStrategy) {
                throw $e;
            }

            if (!$this->getStorage() instanceof  NullStorage) {
                $cacheName = Tools::demodulize(get_class($this->getStorage()));
                $msg = "[{$cacheName}]  {$e->getMessage()}. That's ok, we're loading data from the DB.";
                LoggerService::instance()->warning($msg);
                WarmupScheduler::scheduleWarmup();
            }

            if (Db::instance()->isEnabled()) {
                return $getter->fallback($scope);
            }
            throw $e;
        }
    }

    public function exists($getterName, $scope = null)
    {
        return !!$this->get($getterName, $scope);
    }

    /**
     * @param string $dataName
     * @return DataGetterInterface
     * @throws \Exception
     */
    public function getGetter($dataName)
    {
        if (!isset($this->_dataGetters[$dataName])) {
            throw new \Exception("No registered getters for data name '{$dataName}'");
        }

        return $this->_dataGetters[$dataName];
    }

    public function deleteAll()
    {
        $this->_storage->deleteAll();
    }
}