<?php

namespace Hamlet\JsonMapper\Cars;

use Hamlet\JsonMapper\JsonMapperAware;
use Hamlet\JsonMapper\JsonMapperConfiguration;

class Car implements JsonMapperAware
{
    /** @var string */
    protected $make;

    public function make(): string
    {
        return $this->make;
    }

    public static function configureJsonMapper(JsonMapperConfiguration $configuration): JsonMapperConfiguration
    {
        return $configuration
            ->withTypeResolver(self::class, function ($properties) {
                if (array_key_exists('machineGunCapacity', (array) $properties)) {
                    return JamesBondCar::class;
                } else {
                    return Car::class;
                }
            });
    }
}
