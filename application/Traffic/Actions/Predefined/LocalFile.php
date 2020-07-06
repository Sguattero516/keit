<?php

namespace Traffic\Actions\Predefined;

use Component\Landings\LocalFile\LocalFileService;
use Core\Sandbox\SandboxContext;
use Component\Landings\LocalFile\PageInfo;
use Component\Landings\LocalFile\Validator\IncompatibleLocalFile;
use Component\Landings\LocalFile\PageWrapper;
use Core\Application\Application;
use Traffic\Actions\AbstractAction;
use Traffic\Logging\Service\LoggerService;

class LocalFile extends AbstractAction
{
    const FOLDER = 'folder';
    const NO_INDEX_FILE = 'Error: LP must contain index file. Please read the system log file.';

    public function getType()
    {
        return self::TYPE_HIDDEN;
    }

    public function getField()
    {
        return self::UPLOAD;
    }

    public function _execute()
    {
        $pageContext = new SandboxContext([
            'raw_click' => $this->getRawClick(),
            'server_request' => $this->getServerRequest(),
            'stream' => $this->getStream(),
            'campaign' => $this->getCampaign()
        ]);

        $pageInfo = new PageInfo(
            $this->getServerRequest()->getUri(),
            $this->getActionOptions(),
            LocalFileService::instance()->isPhpAllowed()
        );

        try {
            $response = PageWrapper::wrap($pageInfo, $pageContext);
            $this->setContent($response->getBody());
            $this->setStatus($response->getStatusCode());
            foreach ($response->getHeaders() as $name => $values) {
                $this->header($name, $values);
            }
        } catch (IncompatibleLocalFile $e) {
            LoggerService::instance()->error($e->getMessage());
            if (Application::instance()->isDebug() || Application::instance()->isDevelopment()) {
                $this->setContent($e->getMessage());
            } else {
                $this->setContent(self::NO_INDEX_FILE);
            }
        }
        $this->setDestinationInfo('LP');
    }
}
