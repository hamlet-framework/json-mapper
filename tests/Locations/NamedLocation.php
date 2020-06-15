<?php

namespace Hamlet\JsonMapper\Locations;

class NamedLocation extends Location
{
    /**
     * @var string
     */
    protected $name;

    public function name(): string
    {
        return $this->name;
    }
}
