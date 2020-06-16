<?php

namespace Hamlet\JsonMapper;

use Hamlet\Cast\Resolvers\DefaultResolver;
use Hamlet\Cast\Resolvers\SubTypeResolution;
use Hamlet\Cast\Resolvers\ValueResolution;
use ReflectionException;
use RuntimeException;
use stdClass;

class JsonMapperResolver extends DefaultResolver
{
    /**
     * @var JsonMapperConfiguration
     */
    private $configuration;

    public function __construct(JsonMapperConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @template T
     * @param string $type
     * @psalm-param class-string<T> $type
     * @param mixed $value
     * @param string[] $typesVisited
     * @psalm-param array<class-string<T>> $typesVisited
     * @return SubTypeResolution
     * @psalm-return SubTypeResolution<T>
     * @throws ReflectionException
     *
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function resolveSubType(string $type, $value, array $typesVisited = []): SubTypeResolution
    {
        if ($type[0] == '\\') {
            $type = substr($type, 1);
        }
        if (!in_array($type, $typesVisited)) {
            $reflectionClass = $this->getReflectionClass($type);
            if ($reflectionClass->implementsInterface(JsonMapperAware::class)) {
                /** @var JsonMapperConfiguration $subTreeConfiguration */
                $subTreeConfiguration = $reflectionClass->getMethod('configureJsonMapper')->invoke(null, $this->configuration);
                $typesVisited[] = $type;
                return $subTreeConfiguration->resolver()->resolveSubType($type, $value, $typesVisited);
            }
        }

        $typeResolvers = $this->configuration->typeResolvers();
        if (isset($typeResolvers[$type])) {
            $typeResolver = $typeResolvers[$type];
            if (is_string($typeResolver)) {
                $resolvedSubTypeName = call_user_func([$type, $typeResolver], $value);
                return new SubTypeResolution($this->getReflectionClass($resolvedSubTypeName), $this);
            } else {
                $resolvedSubTypeName = $typeResolver($value);
                return new SubTypeResolution($this->getReflectionClass($resolvedSubTypeName), $this);
            }
        }
        return new SubTypeResolution($this->getReflectionClass($type), $this);
    }

    public function setValue($object, string $propertyName, $value)
    {
        if (is_object($object) && !is_a($object, stdClass::class)) {
            $type = get_class($object);

            $propertySetters = $this->configuration->propertySetters();
            if (isset($propertySetters[$type][$propertyName])) {
                $reflectionClass = $this->getReflectionClass($type);
                $method = $reflectionClass->getMethod($propertySetters[$type][$propertyName]);
                $method->invoke($object, $value);
                return $object;
            }

            $classWidePropertySetters = $this->configuration->classWidePropertySetters();
            if (isset($classWidePropertySetters[$type])) {
                $setterResolver = $this->configuration->classWidePropertySetters()[$type];
                if (is_callable($setterResolver)) {
                    $setter = $setterResolver($propertyName);
                } elseif ($setterResolver == JsonMapper::SETTER_DEFAULT) {
                    $setter = 'set' . ucfirst($propertyName);
                } elseif ($setterResolver == JsonMapper::SETTER_IGNORE) {
                    $setter = JsonMapper::SETTER_IGNORE;
                } else {
                    throw new RuntimeException('Invalid class wide JsonMapper ' . print_r($setterResolver, true));
                }
                if ($setter !== JsonMapper::SETTER_IGNORE) {
                    if (is_string($setter)) {
                        $method = $this->getReflectionClass($type)->getMethod($setter);
                        $method->invoke($object, $value);
                        return $object;
                    } else {
                        throw new RuntimeException('Unsupported setter ' . var_export($setter, true));
                    }
                }
            }
        }

        return parent::setValue($object, $propertyName, $value);
    }

    public function getValue($type, string $propertyName, $source): ValueResolution
    {
        $resolution = null;

        $jsonNames = $this->configuration->jsonNames();
        if (isset($jsonNames[$type][$propertyName])) {
            foreach ($jsonNames[$type][$propertyName] as $jsonProperty) {
                if (is_object($source) && property_exists($source, $jsonProperty)) {
                    $resolution = ValueResolution::success($source->{$jsonProperty});
                    break;
                } elseif (is_array($source) && array_key_exists($propertyName, $source)) {
                    $resolution = ValueResolution::success($source[$jsonProperty]);
                    break;
                }
            }
        } elseif (is_object($source) && property_exists($source, $propertyName)) {
            $resolution = ValueResolution::success($source->{$propertyName});
        } elseif (is_array($source) && array_key_exists($propertyName, $source)) {
            $resolution = ValueResolution::success($source[$propertyName]);
        }

        if ($resolution === null) {
            $defaultValues = $this->configuration->defaultValues();
            if (isset($defaultValues[$type][$propertyName])) {
                $resolution = ValueResolution::success($defaultValues[$type][$propertyName]);
            }
        }

        if ($resolution) {
            $converters = $this->configuration->converters();
            if (isset($converters[$type][$propertyName])) {
                $convertedValue = ($converters[$type][$propertyName])($resolution->value());
                $resolution = ValueResolution::success($convertedValue);
            }
        }

        return $resolution ?? ValueResolution::failure();
    }
}
