<?php
namespace Traffic\HitLimit\Repository;

use Core\Entity\Repository\EntityRepository;
use Traffic\HitLimit\Model\HitRequest;

class HitRequestRepository extends EntityRepository
{
    public function definition()
    {
        return HitRequest::definition();
    }
}