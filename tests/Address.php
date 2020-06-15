<?php

namespace Hamlet\JsonMapper;

class Address
{
    /**
     * @var string
     */
    private $city;

    public function __construct(string $city)
    {
        $this->city = $city;
    }

    public function city(): string
    {
        return $this->city;
    }
}
