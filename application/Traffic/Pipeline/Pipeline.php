<?php

namespace Traffic\Pipeline;

use Traffic\Logging\TrafficLogEntry;
use Traffic\Pipeline\Stage\BuildRawClickStage;
use Traffic\Pipeline\Stage\CheckDefaultCampaignStage;
use Traffic\Pipeline\Stage\CheckParamAliasesStage;
use Traffic\Pipeline\Stage\CheckPrefetchStage;
use Traffic\Pipeline\Stage\CheckSendingToAnotherCampaign;
use Traffic\Pipeline\Stage\ChooseLandingStage;
use Traffic\Pipeline\Stage\ChooseOfferStage;
use Traffic\Pipeline\Stage\ChooseStreamStage;
use Traffic\Pipeline\Stage\ExecuteActionStage;
use Traffic\Pipeline\Stage\FindAffiliateNetworkStage;
use Traffic\Pipeline\Stage\FindCampaignStage;
use Traffic\Pipeline\Stage\GenerateTokenStage;
use Traffic\Pipeline\Stage\PrepareRawClickToStoreStage;
use Traffic\Pipeline\Stage\SaveUniquenessSessionStage;
use Traffic\Pipeline\Stage\StageException;
use Traffic\Pipeline\Stage\StageInterface;
use Traffic\Pipeline\Stage\StoreRawClicksStage;
use Traffic\Pipeline\Stage\UpdateCampaignUniquenessSessionStage;
use Traffic\Pipeline\Stage\UpdateCostsStage;
use Traffic\Pipeline\Stage\UpdateHitLimitStage;
use Traffic\Pipeline\Stage\UpdateParamsFromLandingStage;
use Traffic\Pipeline\Stage\UpdatePayoutStage;
use Traffic\Pipeline\Stage\UpdateRawClickStage;
use Traffic\Pipeline\Stage\UpdateStreamUniquenessSessionStage;
use Traffic\RawClick;

class Pipeline
{
    /**
     * @var StageInterface[]
     */
    private $_stages;
    private $_repeats;
    private $_stagesFrozen = false;

    const LIMIT = 10;

    public function firstLevelStages()
    {
        $this->setStages([
            new CheckPrefetchStage(),
            new BuildRawClickStage(),
            new FindCampaignStage(),
            new CheckDefaultCampaignStage(),
            new UpdateRawClickStage(),
            new CheckParamAliasesStage(),
            new UpdateCampaignUniquenessSessionStage(),
            new ChooseStreamStage(),
            new UpdateStreamUniquenessSessionStage(),
            new ChooseLandingStage(),
            new ChooseOfferStage(),
            new GenerateTokenStage(),
            new FindAffiliateNetworkStage(),
            new UpdateHitLimitStage(),
            new UpdateCostsStage(),
            new UpdatePayoutStage(),
            new ExecuteActionStage(),
            new PrepareRawClickToStoreStage(),
            new SaveUniquenessSessionStage(),
            new CheckSendingToAnotherCampaign(),
            new StoreRawClicksStage(),
        ]);
        return $this;
    }

    public function secondLevelStages()
    {
        $this->setStages([
            new FindCampaignStage(),
            new UpdateParamsFromLandingStage(),
            new CheckDefaultCampaignStage(),
            new CheckParamAliasesStage(),
            new ChooseStreamStage(),
            new ChooseOfferStage(),
            new FindAffiliateNetworkStage(),
            new UpdateCostsStage(),
            new UpdatePayoutStage(),
            new ExecuteActionStage(),
            new CheckSendingToAnotherCampaign(),
            new StoreRawClicksStage(),
        ]);
        return $this;
    }

    public function freezeStages()
    {
        $this->_stagesFrozen = true;
    }

    public function setStages($stages)
    {
        $this->_stages = $stages;
    }

    /**
     *  Request
     */

    /**
     * Payload
     * rawClick: RawClick
     * stream: Stream
     * landing: Landing
     * token: string
     * offer: Offer
     * body: string
     * headers: array
     * @return Payload
     */

    public function start(Payload $payload, TrafficLogEntry $logEntry)
    {
        if (empty($this->_stages)) {
            throw new StageException('No stages set');
        }
        $logEntry->add('Starting pipeline');
        $logEntry->startProfiling();
        $payload = $this->_run($payload, $logEntry);

        $logEntry->stopProfiling('Pipeline execution time');
        return $payload;
    }

    private function _run(Payload $payload, TrafficLogEntry $logEntry)
    {
        foreach ($this->_stages as $stage) {
            $payload = $stage->process($payload, $logEntry);
            if (empty($payload)) {
                throw new StageException(get_class($stage) . ' doesn\'t return payload');
            }

            if (!$payload->getServerRequest()) {
                throw new \Exception(get_class($stage) . ' set serverRequest as null');
            }

            if (!$payload->getResponse()) {
                throw new \Exception(get_class($stage) . ' set response as null');
            }

            if ($payload->isAborted()) {
                break;
            }
        }

        if ($payload->isAborted() && $payload->getForcedCampaignId()) {
            if (!$this->_stagesFrozen) {
                $this->firstLevelStages();
            }
            $this->_repeats++;
            if ($this->_repeats < self::LIMIT) {
                $payload = $this->_preparePayloadForCampaign($payload);
                return $this->_run($payload, $logEntry);
            } else {
                $msg = 'Stream #' . ($payload->getStream() ? $payload->getStream()->getId() : 'X') .
                    ' . in campaign "' . ($payload->getCampaign() ? $payload->getCampaign()->getId() : '') .
                    '" makes infinite recursion. Aborting.';
                $logEntry->add($msg);
                throw new StageException($msg);
            }
        }
        return $payload;
    }


    private function _preparePayloadForCampaign(Payload $payload)
    {
        $rawClick = $payload->getRawClick();

        if (empty($rawClick)) {
            throw new StageException('rawClick is empty');
        }

        $nextRawClick = new RawClick($rawClick->getData());
        if ($payload->getCampaign()) {
            $nextRawClick->setParentCampaignId($payload->getCampaign()->getId());
        }

        $nextRawClick->setParentSubId($rawClick->getSubId());

        $payload->setCampaign(null);
        $payload->setOffer(null);
        $payload->setLanding(null);
        $payload->setStream(null);
        $payload->setLanding(null);
        $payload->setActionPayload(null);
        $payload->setActionType(null);
        $payload->setActionOptions(null);
        $payload->setRawClick($nextRawClick);
        $payload->setForcedStreamId(null);
        $payload->abort(false);
        return $payload;
    }
}
