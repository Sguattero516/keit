<?php
namespace Traffic\HitLimit\Service;

use Core\Entity\Service\EntityService;
use Traffic\HitLimit\Model\HitRequest;

class HitRequestService extends EntityService
{
   public function definition()
   {
       return HitRequest::definition();
   }
}