# Hamlet Framework / Json Mapper

[![Build Status](https://travis-ci.org/hamlet-framework/json-mapper.svg)](https://travis-ci.org/hamlet-framework/json-mapper)

Quick Summary:

* _Psalm_ type specifications including object-like-arrays, union types, associative arrays etc.
* _PHP-Parser_ for resolving FQCN
* Uses reflection by default
* Reusable code-as-configuration
* Supports polymorphism through subtype resolvers 
* Cascading configuration options for subtree resolutions
* Type safety: _Psalm_ will know that `JsonMapper::map(_list(_class(User::class)), ...)` returns `list<User>`.
* Cast exception thrown for impossible casting

As a start, to map the following JSON structure:

```json
[
    { "name": "Yuri" },
    { "name": "Oleg", "email": "oleg@example.com", "address": { "city": "Vologda" } }
]
```

into the following class hierarchy:

```php
<?php
 
class User
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $email;

    /** @var Address|null */
    private $address;
}

class Address 
{
    /** @var string */
    private $city;
}
```

use:

```php
<?php

$users = JsonMapper::map(
    _list(_class(User::class)), 
    json_decode($data)
);
```

The library uses [hamlet-framework/type](https://github.com/hamlet-framework/type) library for type specifications.

### Configuration Options

The third parameter of the `JsonMapper::map` is `JsonMapperConfiguration` used to customize the mapping process.

### Json Property

```php
<?php

$configuration
    ->withDefaultValue(User::class, 'name', 'unknown')
    ->withJsonName(User::class, 'homeAddress', 'home_address', 'homeaddress')
    ->ingnoreUnknown(User::class);
```

### Using Setters

```php
<?php

$configuaration
    ->withPropertySetters(User::class)
    ->withPropertySetter(User::class, 'homeAddress', 'updateHomeAddress');
```

### Using Converter

```php
<?php

class User 
{
    /** @var DateTimeImmutable */
    private $time;

    /** @var array<string,string> */
    private $preferences;

    /** @var string|null */
    private $email;
}

$json = '
    { "time": 1593479541, "preferences": "{\"timeZone\":\"Russia/Moscow\"}", "email": "_.oO000_" }
';

$configuration
    ->withConverter(User::class, 'time', function (int $unixtime) {
        return DateTimeImmutable::createFomFormat('U', (string) $unixtime);
    })
    ->withConverter(User::class, 'preferences', function (string $json) {
        return _map(_string(), _string())->cast(json_decode($json)); 
    })
    ->withConverter(self::class, 'email', function ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ?: null;
    });

$user = JsonMapper::map(_class(User::class), json_decode($json), $configuration);

$user->preferences['timeZone'] == 'Russia/Moscow';
$user->time instanceof DateTimeImmutable;
$user->email === null;
```

### Using Type Dispatcher 

```php
<?php

$configuration
    ->withTypeDispatcher(User::class, function ($properties) {
        if (isset($properties['name'])) {
            return NamedUser::class;
        } else {
            return AnonymousUser::class;
        }
    });
```

```php
<?php

$coniguration
    ->withTypeDispatcher(User::class, '__resolveType');
```

### Using JsonMapperAware interface

If you want to keep your mapping configuration closer to the files you map, there's an option to implement `JsonMapperAware` interface

```php
<?php

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

$cars = JsonMapper::map(_list(_class(Car::class)), json_decode($payload));
```

## To do

- Add support for `ignoreUnknown`
- Add support for constructor methods
- Add validators
- Add examples with psalm specs
