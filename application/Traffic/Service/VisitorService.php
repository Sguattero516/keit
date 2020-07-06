<?php
namespace Traffic\Service;

use Component\Clicks\Model\Visitor;
use Core\Entity\Service\EntityService;
use Traffic\RawClick;

class VisitorService extends EntityService
{
    public function definition()
    {
        return Visitor::definition();
    }

    public function generateCode(RawClick $rawClick)
    {
        $srcString = $rawClick->getIpString();
        $srcString .= $rawClick->getUserAgent();
        $srcString .= $rawClick->getConnectionType();
        $srcString .= $rawClick->getCountry();
        $srcString .= $rawClick->getCity();
        $srcString .= $rawClick->getDeviceModel();

        return murmurhash3($srcString);
    }
}
