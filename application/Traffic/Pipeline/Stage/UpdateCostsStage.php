<?php

namespace Traffic\Pipeline\Stage;

use Traffic\Logging\TrafficLogEntry;
use Traffic\Pipeline\Payload;

class UpdateCostsStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $campaign = $payload->getCampaign();
        $rawClick = $payload->getRawClick();
        $request = $payload->getServerRequest();

        if (empty($campaign)) {
            throw new StageException('No campaign');
        }

        if (empty($rawClick)) {
            throw new StageException('No rawClick');
        }

        if ($campaign->isCostAuto()) {
            $cost = str_replace(',', '.', $rawClick->get('cost'));
            $cost = $this->_patchMegapush($cost);
            $currency = $rawClick->get('currency');
        } else {
            $cost = $campaign->getCostValue();
            $currency = $campaign->getCostCurrency();
        }
        $rawClick->setCost(0);
        
        if (!empty($cost) && !is_numeric($cost)) {
            $logEntry->add('Incorrect cost received - ' . $cost);
            return $payload;
        }

        $cost = $this->_applyTrafficLoss($cost, $campaign->getTrafficLoss());

        switch (true) {
            case $campaign->isCostPerAcquisition() || $campaign->isCostPerSale() || $campaign->isCostRevShare():
                return $payload;
            case $campaign->isCostPerUnique():
                if ($rawClick->isUniqueCampaign()) {
                    $rawClick->setCost($cost, $currency);
                }
                return $payload;
            case $campaign->isCostPerThousand() && $cost:
                $rawClick->setCost($cost / 1000, $currency);
                break;
            case $campaign->isCostPerClick() && $cost:
                $rawClick->setCost($cost, $currency);
                break;
        }

        return $payload;
    }

    private function _applyTrafficLoss($cost, $trafficLossValue)
    {
        if ($cost && $trafficLossValue) {
            return $cost / (1 - ($trafficLossValue / 100));
        } else {
            return $cost;
        }
    }

    private function _patchMegapush($cost)
    {
        if (is_string($cost) && preg_match('/^00[0-9]+/', $cost)) {
            $cost = preg_replace('/^00/', '0.', $cost);
        }
        return $cost;
    }
}