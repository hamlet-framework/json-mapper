<?php

namespace Hamlet\JsonMapper;

interface JsonMapperAware
{
    public static function configureJsonMapper(JsonMapperConfiguration $configuration): JsonMapperConfiguration;
}
