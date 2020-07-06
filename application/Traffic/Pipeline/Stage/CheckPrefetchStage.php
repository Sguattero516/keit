<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Repository\CachedSettingsRepository;
use Traffic\Response\ResponseFactory;
use Traffic\Service\SettingsService;
use Traffic\Request\ServerRequest;
use Traffic\Pipeline\Payload;
use Traffic\Logging\TrafficLogEntry;

class CheckPrefetchStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $request = $payload->getServerRequest();

        if (empty($request)) {
            throw new StageException('Empty request');
        }

        if (!CachedSettingsRepository::instance()->get('ingore_prefetch')) {
            return $payload;
        }

        if ($this->_isPrefetchDetected($request) || ($request->getParam('version') && $request->getParam('prefetch'))) {
            $logEntry->add('Ignored because prefetch is detected');
            $payload->setResponse(ResponseFactory::build([
                'body' => 'Prefetch not allowed',
                'status' => 403
            ]));
            $payload->abort();
        }

        return $payload;
    }

    private function _isPrefetchDetected(ServerRequest $request)
    {
        $checkServerParams = ['X-PURPOSE' => 'preview', 'X-MOZ' => 'prefetch', 'X-FB-HTTP-ENGINE' => 'Liger'];
        foreach ($checkServerParams as $name => $value) {
            if ($request->getHeaderLine($name) == $value) {
                return true;
            }
        }
        return false;
    }
}