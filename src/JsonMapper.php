<?php

namespace Hamlet\JsonMapper;

use Hamlet\Cast\Type;

final class JsonMapper
{
    const SETTER_DEFAULT = 0;
    const SETTER_IGNORE = 1;

    private function __construct()
    {
    }

    /**
     * @template T
     * @param Type $type
     * @psalm-param Type<T> $type
     * @param array|object|string $data
     * @param JsonMapperConfiguration|null $configuration
     * @return mixed
     * @psalm-return T
     */
    public static function map(Type $type, $data, JsonMapperConfiguration $configuration = null)
    {
        $configuration = $configuration ?? JsonMapperConfiguration::default();
        return $type->resolveAndCast($data, $configuration->resolver());
    }
}
