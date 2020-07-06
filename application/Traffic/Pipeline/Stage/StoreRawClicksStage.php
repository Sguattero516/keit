<?php
namespace Traffic\Pipeline\Stage;

use Component\Clicks\DelayedCommand\AddClickCommand;
use Traffic\Logging\Service\LoggerService;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Repository\CachedSettingsRepository;
use Traffic\Service\SettingsService;

class StoreRawClicksStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $clicks = $payload->getRawClicksToStore();
        $logEntry->add('Saving clicks: ' . count($clicks) );

        if (!empty($payload->getStream()) && !$payload->getStream()->get('collect_clicks')) {
            $logEntry->add('Stream doesn\'t store clicks. Skipping.');
            return $payload;
        }

        if (CachedSettingsRepository::instance()->get('disable_stats')) {
            $logEntry->add('Statistics disabled. Skipping.');
            return $payload;
        }


        foreach ($clicks as $rawClick) {
            try {
                AddClickCommand::saveClick($rawClick);
            } catch (\ADODB_Exception $e) {
                LoggerService::instance()->error($e->getMessage());
            }
        }
        return $payload;
    }
}