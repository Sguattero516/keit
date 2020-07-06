<?php
namespace Traffic\Pipeline\Stage;

use Traffic\Logging\TrafficLogEntry;
use Traffic\Model\Click;
use Traffic\Pipeline\Payload;
use Traffic\Tools\Tools;

class UpdateParamsFromLandingStage implements StageInterface
{
    public function process(Payload $payload, TrafficLogEntry $logEntry)
    {
        $allowedParams = [
            'creative_id'
        ];

        for ($num = 1; $num <= Click::getSubIdCount(); $num++) {
            $allowedParams[] = 'sub_id_' . $num;
        }

        $request = $payload->getServerRequest();
        $rawClick = $payload->getRawClick();

        foreach ($allowedParams as $paramName) {
            if ($request->hasParam($paramName)) {
                $rawClick->set($paramName, Tools::utf8ize($request->getParam($paramName)));
            }
        }

        $payload->setRawClick($rawClick);

        return $payload;
    }
}