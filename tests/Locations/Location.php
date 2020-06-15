<?php

namespace Hamlet\JsonMapper\Locations;

use Hamlet\JsonMapper\Restaurants\Restaurant;
use Hamlet\JsonMapper\Restaurants\ThemeRestaurant;

class Location
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Restaurant[]|null
     */
    protected $restaurants;

    public function id(): int
    {
        return $this->id;
    }

    public function restaurants(): array
    {
        return $this->restaurants ?? [];
    }

    public static function __resolveType($properties)
    {
        if (array_key_exists('name', (array) $properties)) {
            return NamedLocation::class;
        } else {
            return Location::class;
        }
    }
}
