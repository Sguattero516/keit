<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Repository\CachedSettingsRepository;
use Traffic\Service\SettingsService;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;

class PrepareRawClickToStoreStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $rawClick = $payload->getRawClick();
        $stream = $payload->getStream();

        if (empty($rawClick)) {
            throw new StageException('Empty rawClick');
        }

        if ($stream && !$stream->get('collect_clicks')) {
            return $payload;
        }

        if (CachedSettingsRepository::instance()->get('disable_stats', false)) {
            return $payload;
        }

        $payload->addRawClickToStore($rawClick);

        return $payload;
    }
}