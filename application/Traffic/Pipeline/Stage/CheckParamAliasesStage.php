<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Repository\ParameterRepository;
use Traffic\Tools\Tools;
use Traffic\Model\Click;
use Traffic\Pipeline\Payload;
use Traffic\RawClick;
use Traffic\Request\ServerRequest;
use Traffic\Logging\TrafficLogEntry;

class CheckParamAliasesStage implements StageInterface
{
    private $_paramsWithAliases = [
        'se_referrer',
        'source',
        'keyword',
        'se',
        'landing_id',
        'creative_id',
        'ad_campaign_id',
        'external_id',
        'cost',
        'currency',
    ];

    const SITE = 'site';

    public function __construct()
    {
        for ($i = 1; $i <= Click::getSubIdCount(); $i++) {
            $this->_paramsWithAliases[] = 'sub_id_' . $i;
        }
        for ($i = 1; $i <= Click::EXTRA_PARAM_COUNT; $i++) {
            $this->_paramsWithAliases[] = 'extra_param_' . $i;
        }
    }

    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $campaign = $payload->getCampaign();
        $rawClick = $payload->getRawClick();
        $request = $payload->getServerRequest();
        if (empty($campaign)) {
            throw new StageException('Empty campaign');
        }

        if (empty($rawClick)) {
            throw new StageException('Empty rawClick');
        }

        if (empty($request)) {
            throw new StageException('Empty request');
        }

        $this->_checkAliasesFromSettings($request, $rawClick, $logEntry);
        $this->_checkAliasesFromCampaign($request, $rawClick, $campaign->getParameters(), $logEntry);
        $this->_checkSiteAlias($request, $rawClick, $campaign->getParameters(), $logEntry);
        $this->_checkPlaceholderFromCampaign($request, $rawClick, $campaign->getParameters(), $logEntry);
        return $payload;
    }

    protected function _checkAliasesFromSettings(ServerRequest $request, RawClick $rawClick, TrafficLogEntry $logger)
    {
        foreach ($this->_paramsWithAliases as $paramName) {
            $oldParam = $request->getParam($paramName);
            if (!is_null($oldParam)) {
                continue;
            }
            foreach (ParameterRepository::instance()->getAliasesFor($paramName) as $alias) {
                if ($request->hasParam($alias) && $paramName != $alias) {
                    $logger->add('Param alias matched ' . $alias . ' -> ' . $paramName);
                    $rawClick->set($paramName, Tools::utf8ize($request->getParam($alias)));
                    break;
                }
            }
        }
    }

    protected function _checkAliasesFromCampaign(ServerRequest $request, RawClick $rawClick, $parameters, TrafficLogEntry $logger)
    {
        if (empty($parameters)) {
            return;
        }
        foreach ($parameters as $paramName => $valueArr) {
            $alias = !empty($valueArr['name']) ? $valueArr['name'] : $paramName;
            if ($request->hasParam($alias) && $paramName != $alias) {
                $logger->add('Param alias matched ' . $alias . ' -> ' . $paramName);
                $rawClick->set($paramName, Tools::utf8ize($request->getParam($alias)));
            }
        }
    }

    protected function _checkSiteAlias(ServerRequest $request, RawClick $rawClick, $parameters, TrafficLogEntry $logger)
    {
        if (!empty($request->getParam(self::SITE))) {
            $rawClick->set('source', $request->getParam(self::SITE));
        }
    }

    protected function _checkPlaceholderFromCampaign(ServerRequest $request, RawClick $rawClick, $parameters, TrafficLogEntry $logger)
    {
        if (empty($parameters)) {
            return;
        }
        foreach ($parameters as $paramName => $valueArr) {
            if (empty($valueArr['placeholder'])) {
                continue;
            }
            $placeholder = $valueArr['placeholder'];
            $alias = !empty($valueArr['name']) ? $valueArr['name'] : $paramName;
            if (!$request->hasParam($alias) && !$request->hasParam($paramName) && empty($rawClick->get($paramName))) {
                $logger->add(
                    'No value provided for ' . $paramName .
                    '. Setting value from placeholder -> ' . $placeholder
                );
                $rawClick->set($paramName, $placeholder);
            }
        }
    }
}