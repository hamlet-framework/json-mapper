<?php

namespace Hamlet\JsonMapper\Weather;

use Hamlet\JsonMapper\JsonMapperAware;
use Hamlet\JsonMapper\JsonMapperConfiguration;

class Forecast implements JsonMapperAware
{
    /** @var int */
    private $time;

    /** @var float|null */
    private $maxTemperature;

    /** @var float|null */
    private $minTemperature;

    /** @var float */
    private $precipitation;

    public function maxTemperature()
    {
        return $this->maxTemperature;
    }

    public function minTemperature()
    {
        return $this->minTemperature;
    }

    public function precipitation(): float
    {
        return $this->precipitation;
    }

    public static function configureJsonMapper(JsonMapperConfiguration $configuration): JsonMapperConfiguration
    {
        $temperatureConverter = function ($value) {
            if ($value == '-999' || !is_numeric($value)) {
                return null;
            } else {
                return (float) $value;
            }
        };

        return $configuration
            ->withJsonName(self::class, 'maxTemperature', 'max_temp')
            ->withJsonName(self::class, 'minTemperature', 'min_temp', 'minimum_temp')
            ->withJsonName(self::class, 'precipitation', 'precip')
            ->withDefaultValue(self::class, 'precipitation', 0.0)
            ->withConverter(self::class, 'maxTemperature', $temperatureConverter)
            ->withConverter(self::class, 'minTemperature', $temperatureConverter)
            ->withConverter(self::class, 'precipitation', function ($value) {
                if (preg_match('|^\s*\d+(\.\d+)?%\s*$|', $value)) {
                    return ((float) $value) / 100;
                } else {
                    return null;
                }
            });
    }
}
