<?php
namespace Traffic\Model\Mixin;

use Core\Entity\State;

trait StateMethodsTrait
{
    public function getState()
    {
        return $this->get('state');
    }

    public function setState($value)
    {
        $this->set('state', $value);
        return $this;
    }

    public function isActive()
    {
        return $this->get('state') == State::ACTIVE;
    }

    public function isDeleted()
    {
        return $this->get('state') === State::DELETED;
    }
}