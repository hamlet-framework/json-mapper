<?php

namespace Hamlet\JsonMapper\Restaurants;

class Restaurant
{
    /** @var string */
    protected $name;

    /** @var int */
    protected $seats;

    public function name(): string
    {
        return $this->name;
    }

    public function seats(): int
    {
        return $this->seats;
    }

    public static function __resolveType($properties)
    {
        if (array_key_exists('theme', (array) $properties)) {
            return ThemeRestaurant::class;
        } else {
            return Restaurant::class;
        }
    }
}
