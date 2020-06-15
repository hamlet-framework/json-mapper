# Hamlet Framework / Json Mapper

[![Build Status](https://travis-ci.org/hamlet-framework/json-mapper.svg)](https://travis-ci.org/hamlet-framework/json-mapper)

To map the following JSON structure:

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

```
$configuration
    ->withDefaultValue(User::class, 'name', 'unknown')
    ->withJsonName(User::class, 'homeAddress', 'home_address', 'homeaddress')
    ->ingnoreUnknown(User::class);
```

### Using Setters

```
$configuaration
    ->withPropertySetters(User::class)
    ->withPropertySetter(User::class, 'homeAddress', 'updateHomeAddress');
```

### Using Converter

```
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
```

### Using Type Dispatcher 

```
$configuration
    ->withTypeDispatcher(User::class, function ($properties) {
        if (isset($properties['name'])) {
            return NamedUser::class;
        } else {
            return AnonymousUser::class;
        }
    });
```

```
$coniguration
    ->withTypeDispatcher(User::class, '__resolveType');
```

