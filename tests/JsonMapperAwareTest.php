<?php

namespace Hamlet\JsonMapper;

use Hamlet\JsonMapper\Weather\Forecast;
use PHPUnit\Framework\TestCase;
use function Hamlet\Cast\_class;
use function Hamlet\Cast\_list;

class JsonMapperAwareTest extends TestCase
{
    public function testMappingWithExternalNames()
    {
        $json = json_decode(file_get_contents(__DIR__ . '/Weather/feed.json'));
        $forecast = JsonMapper::map(_list(_class(Forecast::class)), $json);

        $this->assertCount(2, $forecast);

        $this->assertSame(23.94, $forecast[0]->maxTemperature());
        $this->assertSame(0.19, $forecast[0]->precipitation());

        $this->assertNull($forecast[1]->maxTemperature());
    }
}
