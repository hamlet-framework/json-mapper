<?php

namespace Hamlet\JsonMapper\Cars;

class JamesBondCar extends Car
{
    /** @var int */
    protected $machineGunCapacity;

    public function machineGunCapacity(): int
    {
        return $this->machineGunCapacity;
    }
}
