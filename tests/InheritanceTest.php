<?php

namespace Hamlet\JsonMapper;

use Hamlet\JsonMapper\Cars\Car;
use Hamlet\JsonMapper\Cars\JamesBondCar;
use Hamlet\JsonMapper\Locations\Location;
use Hamlet\JsonMapper\Locations\NamedLocation;
use Hamlet\JsonMapper\Restaurants\Restaurant;
use Hamlet\JsonMapper\Restaurants\ThemeRestaurant;
use PHPUnit\Framework\TestCase;
use function Hamlet\Cast\_class;
use function Hamlet\Cast\_list;

class InheritanceTest extends TestCase
{
    public function testInlineTypeDispatcher()
    {
        $data = '
            [
                { "id": 1, "name": "Sevastopol" },
                { "id": 2 }
            ]
        ';

        $configuration = JsonMapperConfiguration::default()
            ->withTypeResolver(Location::class, function ($properties) {
                if (array_key_exists('name', (array) $properties)) {
                    return NamedLocation::class;
                } else {
                    return Location::class;
                }
            });
        $locations = JsonMapper::map(_list(_class(Location::class)), json_decode($data), $configuration);

        $this->assertCount(2, $locations);
        $this->assertInstanceOf(NamedLocation::class, $locations[0]);
        $this->assertInstanceOf(Location::class, $locations[1]);
        $this->assertEquals('Sevastopol', $locations[0]->name());
    }

    public function testReferencedTypeDispatcher()
    {
        $data = '
            [
                { "name": "Lonely Horse", "seats": 234, "theme": "Weltschmerz" },
                { "name": "Hipster Shoelaces", "seats": 4 }
            ]
        ';

        $configuration = JsonMapperConfiguration::default()
            ->withTypeResolver(Restaurant::class, '__resolveType');
        $restaurants = JsonMapper::map(_list(_class(Restaurant::class)), json_decode($data), $configuration);

        $this->assertCount(2, $restaurants);
        $this->assertInstanceOf(ThemeRestaurant::class, $restaurants[0]);
        $this->assertInstanceOf(Restaurant::class, $restaurants[1]);
    }

    public function testNestedTypeDispatchers()
    {
        $data = '
            [
                { "id": 1, "name": "Sevastopol", "restaurants": [
                    { "name": "Lonely Horse", "seats": 234, "theme": "Weltschmerz" },
                    { "name": "Hipster Shoelaces", "seats": 4 }
                ] },
                { "id": 2 }
            ]
        ';

        $configuration = JsonMapperConfiguration::default()
            ->withTypeResolver(Location::class, '__resolveType')
            ->withTypeResolver(Restaurant::class, '__resolveType');
        $locations = JsonMapper::map(_list(_class(Location::class)), json_decode($data), $configuration);

        $this->assertCount(2, $locations);
        $this->assertInstanceOf(NamedLocation::class, $locations[0]);
        $this->assertInstanceOf(Location::class, $locations[1]);

        $restaurants = $locations[0]->restaurants();
        $this->assertCount(2, $restaurants);
        $this->assertInstanceOf(ThemeRestaurant::class, $restaurants[0]);
        $this->assertInstanceOf(Restaurant::class, $restaurants[1]);
    }

    public function testInheritanceThroughJsonMapperAware()
    {
        $data = '
            [
                { "make": "Great Wall", "maxSpeed": 346.87 },
                { "make": "Lamborghini", "maxSpeed": 346.01 },
                { "make": "Bentley", "maxSpeed": 73.4, "machineGunCapacity": 17}
            ]
        ';

        $cars = JsonMapper::map(_list(_class(Car::class)), json_decode($data, true));

        $this->assertCount(3, $cars);
        $this->assertInstanceOf(Car::class, $cars[0]);
        $this->assertInstanceOf(Car::class, $cars[1]);
        $this->assertInstanceOf(JamesBondCar::class, $cars[2]);
    }
}
