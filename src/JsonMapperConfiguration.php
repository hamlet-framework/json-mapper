<?php

namespace Hamlet\JsonMapper;

class JsonMapperConfiguration
{
    /**
     * @var array[]
     * @psalm-var array<string,array<string,mixed>>
     */
    private $defaultValues = [];

    /**
     * @var array[]
     * @psalm-var array<string,array<string,string[]>>
     */
    private $jsonNames = [];

    /**
     * @var array
     * @psalm-var array<string,callable|int>
     */
    private $classWidePropertySetters = [];

    /**
     * @var array
     * @psalm-var array<string,array<string,string>>
     */
    private $propertySetters = [];

    /**
     * @var array
     * @psalm-var array<string,array<string,callable>>
     */
    private $converters = [];

    /**
     * @var callable[]
     * @psalm-var array<string,string|callable>
     */
    private $typeResolvers = [];

    private function __construct()
    {
    }

    public static function default(): self
    {
        return new self;
    }

    /**
     * @template T
     * @template V
     * @param string $type
     * @psalm-param class-string<T> $type
     * @param string $property
     * @param $value
     * @psalm-param V $value
     * @return self
     */
    public function withDefaultValue(string $type, string $property, $value): self
    {
        $copy = clone $this;
        $copy->defaultValues[$type][$property] = $value;
        return $copy;
    }

    /**
     * @template T
     * @param string $type
     * @psalm-param class-string<T> $type
     * @param string $property
     * @param string ...$names
     * @return self
     */
    public function withJsonName(string $type, string $property, string ...$names): self
    {
        $copy = clone $this;
        $copy->jsonNames[$type][$property] = $names;
        return $copy;
    }

    /**
     * @template T
     * @param string $type
     * @psalm-param class-string<T> $type
     * @param callable|int $setterNameResolver
     * @return self
     */
    public function withPropertySetters(string $type, $setterNameResolver = JsonMapper::SETTER_DEFAULT): self
    {
        $copy = clone $this;
        $copy->classWidePropertySetters[$type] = $setterNameResolver;
        return $copy;
    }

    /**
     * @template T
     * @param string $type
     * @psalm-param class-string<T> $type
     * @param string $property
     * @param string $setterName
     * @return self
     */
    public function withPropertySetter(string $type, string $property, string $setterName): self
    {
        $copy = clone $this;
        $copy->propertySetters[$type][$property] = $setterName;
        return $copy;
    }

    /**
     * @template T
     * @param string $type
     * @psalm-param class-string<T> $type
     * @param string $property
     * @param callable $converter
     * @psalm-param callable(mixed):mixed $converter
     * @return self
     */
    public function withConverter(string $type, string $property, callable $converter): self
    {
        $copy = clone $this;
        $copy->converters[$type][$property] = $converter;
        return $copy;
    }

    /**
     * @template T
     * @param string $type
     * @psalm-param class-string<T> $type
     * @param callable|string $typeResolver
     * @psalm-param string|callable $typeResolver
     * @return self
     */
    public function withTypeResolver(string $type, $typeResolver): self
    {
        $copy = clone $this;
        $copy->typeResolvers[$type] = $typeResolver;
        return $copy;
    }

    public function resolver(): JsonMapperResolver
    {
        return new JsonMapperResolver($this);
    }

    public function defaultValues(): array
    {
        return $this->defaultValues;
    }

    public function jsonNames(): array
    {
        return $this->jsonNames;
    }

    public function classWidePropertySetters(): array
    {
        return $this->classWidePropertySetters;
    }

    public function propertySetters(): array
    {
        return $this->propertySetters;
    }

    public function converters(): array
    {
        return $this->converters;
    }

    public function typeResolvers(): array
    {
        return $this->typeResolvers;
    }
}
