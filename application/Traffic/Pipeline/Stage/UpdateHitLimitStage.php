<?php
namespace Traffic\Pipeline\Stage;

use Traffic\HitLimit\Service\HitLimitService;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Model\BaseStream;
use Traffic\Pipeline\Payload;
use Traffic\Repository\CachedStreamFilterRepository;

class UpdateHitLimitStage implements StageInterface
{
    const LIMIT = 'limit';

    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $stream = $payload->getStream();
        $rawClick = $payload->getRawClick();

        if (empty($rawClick)) {
            throw new StageException('Empty rawClick');
        }

        if ($stream && $this->hasLimitFilter($stream)) {
            HitLimitService::instance()->store($stream, $rawClick->getDateTime());
        }
        return $payload;
    }

    public function hasLimitFilter(BaseStream $stream)
    {
        $filters = CachedStreamFilterRepository::instance()->allCached($stream);

        if ($filters && is_array($filters)) {
            foreach ($filters as $filter) {
                if ($filter->getName() == self::LIMIT) {
                    return true;
                }
            }
        }
        return false;
    }
}