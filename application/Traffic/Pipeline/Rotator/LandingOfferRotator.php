<?php
namespace Traffic\Actions;

use Core\Entity\State;
use Traffic\Cache\NoCache;
use Traffic\Logging\Service\LoggerService;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Model\Landing;
use Traffic\Model\Offer;
use Traffic\Repository\AbstractBaseRepository;
use Core\Entity\Model\EntityModelInterface;
use Traffic\Cookies\Service\CookiesService;
use Traffic\Pipeline\Stage\StageException;
use Traffic\Request\ServerRequest;
use Traffic\Response\Response;

class LandingOfferRotator
{
    private $_cookieName;
    /**
     * @var AbstractBaseRepository
     */
    private $_repository;
    private $_bindEnabled;
    private $_associationField;
    private $_cookieTtl;

    public static function factory($opts)
    {
        return new LandingOfferRotator(
            $opts['cookieName'],
            $opts['repository'],
            $opts['bindEnabled'],
            $opts['cookieTtl'],
            $opts['associationField']
        );
    }

    public function __construct($cookieName, AbstractBaseRepository $repository, $bindEnabled, $cookieTtl, $associationField)
    {
        $this->_cookieName = $cookieName;
        $this->_repository = $repository;
        $this->_bindEnabled = $bindEnabled;
        $this->_cookieTtl = $cookieTtl;
        $this->_associationField = $associationField;
    }

    private function _getCookieName()
    {
        return $this->_cookieName;
    }

    private function _getCookieNameWithPrefix()
    {
        return substr(md5(SALT), 5, 8) . $this->_getCookieName();
    }

    private function _getRepository()
    {
        return $this->_repository;
    }

    protected function _isBindVisitorsEnabled()
    {
        return $this->_bindEnabled;
    }

    private function _getEntityFromAssociation(EntityModelInterface $item)
    {
        return $this->_getRepository()->findCached($item->get($this->_associationField));
    }

    /**
     * @param $serverRequest ServerRequest
     * @param $associations
     * @param $logEntry TrafficLogEntry
     * @return Offer|Landing|null
     */
    public function getRandom(ServerRequest $serverRequest, $associations, TrafficLogEntry $logEntry)
    {
        if (empty($associations)) {
            LoggerService::instance()->debug("[Rotator] no more associations");
            return null;
        }

        $entity = null;
        $association = null;

        if ($this->_isBindVisitorsEnabled()) {
            $entityId = $this->_findBoundEntityId($serverRequest);
            LoggerService::instance()->debug("[Rotator] bound to {$entityId}");
            $association = $this->_findAssociationByEntityId($associations, $entityId);
        }

        if (empty($association)) {
            $association = $this->_rollDice($associations);
        }

        LoggerService::instance()->debug(function() use ($association) {
            return "[Rotator] Get association: " . json_encode($association->getData());
        });

        if (!$this->_isAssociationOk($association)) {
            LoggerService::instance()->debug(function() use ($association) {
                return "[Rotator] Association is not ok: " . json_encode($association->getData());
            });
            $associations = $this->_deleteAssociation($associations, $association);
            return $this->getRandom($serverRequest, $associations, $logEntry);
        }

        $entity = $this->_getEntityFromAssociation($association);

        if (!$this->_isEntityOk($entity)) {
            LoggerService::instance()->debug(function() use ($entity) {
                return "[Rotator] Entity is not ok: " . json_encode($entity->getData());
            });

            $associations = $this->_deleteAssociation($associations, $association);
            return $this->getRandom($serverRequest, $associations, $logEntry);
        }

        LoggerService::instance()->debug(function() use ($entity) {
            return "[Rotator] Got entity: " . json_encode($entity->getData());
        });

        return $entity;
    }

    private function _rollDice($items)
    {
        if (!is_array($items) || empty($items)) {
            return null;
        }

        shuffle($items);

        $totalWeight = 0;
        $selected  = 0;

        foreach ($items as $i => $item) {
            $weight = $item->getShare();
            $rand = mt_rand(0, $totalWeight + $weight);
            if ($rand >= $totalWeight) {
                $selected = $i;
            }
            $totalWeight += $weight;
        }

        $selectedItem = $items[$selected];
        if ($selectedItem) {
            return $selectedItem;
        } else {
            unset($items[$selected]);
            return $this->_rollDice($items);
        }
    }

    private function _deleteAssociation($associations, $association)
    {
        $index = array_search($association, $associations);
        unset($associations[$index]);
        return $associations;
    }

    private function _isAssociationOk($association)
    {
        return !empty($association) && $association->getState() == State::ACTIVE && $association->getShare();
    }

    private function _isEntityOk($entity)
    {
        return $entity && $entity->getState() == State::ACTIVE;
    }

    private function _findBoundEntityId(ServerRequest $serverRequest)
    {
        return CookiesService::instance()->decodeAndGet($serverRequest, $this->_getCookieNameWithPrefix());
    }

    private function _findAssociationByEntityId($associations, $entityId)
    {
        if (empty($associations)) {
            return null;
        }

        if (empty($entityId)) {
            return null;
        }

        foreach ($associations as $association) {
            if ($association->get($this->_associationField) == $entityId) {
                return $association;
            }
        }
        return null;
    }

    /**
     * @param ServerRequest $serverRequest
     * @param Response $response
     * @param EntityModelInterface $entity
     * @return ServerRequest[]|Response[]
     */
    public function bindToEntity(ServerRequest $serverRequest, Response $response, EntityModelInterface $entity)
    {
        return CookiesService::instance()->encodeAndSet($serverRequest, $response, $this->_getCookieNameWithPrefix(), $entity->getId(), $this->_getCookieTtlInSeconds());
    }

    private function _getCookieTtlInSeconds()
    {
        return time() + 60 + 60 * $this->_cookieTtl;
    }
}