<?php

namespace Hamlet\JsonMapper;

use Hamlet\Cast\CastException;
use PHPUnit\Framework\TestCase;
use function Hamlet\Cast\_class;
use function Hamlet\Cast\_list;
use function Hamlet\Cast\_map;
use function Hamlet\Cast\_string;

class JsonMapperTest extends TestCase
{
    public function testSimpleMapping()
    {
        $data = '
            [
                { "name": "Yuri" },
                { "name": "Oleg", "email": "oleg@example.com", "address": { "city": "Vologda" } }
            ]
        ';
        $users = JsonMapper::map(_list(_class(User::class)), json_decode($data));

        $this->assertCount(2, $users);
        $this->assertInstanceOf(User::class, $users[0]);

        $this->assertEquals('Yuri', $users[0]->name());
        $this->assertNull($users[0]->email());

        $this->assertEquals('Oleg', $users[1]->name());
        $this->assertEquals('oleg@example.com', $users[1]->email());
    }

    public function testNullOnNonNullablePropertiesRaisesException()
    {
        $this->expectException(CastException::class);
        $data = '
            { "email": "Valentin" }
        ';
        JsonMapper::map(_class(User::class), json_decode($data));
    }

    public function testDefaultValue()
    {
        $data = '
            { "name": "Sergey" }
        ';
        $configuration = JsonMapperConfiguration::default()
            ->withDefaultValue(User::class, 'email', 'default@example.com');

        $user = JsonMapper::map(_class(User::class), json_decode($data), $configuration);

        $this->assertEquals('Sergey', $user->name());
        $this->assertEquals('default@example.com', $user->email());
    }

    public function testJsonName()
    {
        $data = '
            { "name": "Anton", "e_mail": "anton@example.com" }
        ';
        $configuration = JsonMapperConfiguration::default()
            ->withJsonName(User::class, 'email', 'e_mail');

        $user = JsonMapper::map(_class(User::class), json_decode($data), $configuration);

        $this->assertEquals('Anton', $user->name());
        $this->assertEquals('anton@example.com', $user->email());
    }

    public function testWithPropertySetters()
    {
        $data = '
            { "name": "Anton", "email": "anton@example.com" }
        ';
        $configuration = JsonMapperConfiguration::default()
            ->withPropertySetters(User::class);

        $user = JsonMapper::map(_class(User::class), json_decode($data), $configuration);

        $this->assertEquals('ANTON', $user->name());
        $this->assertEquals('ANTON@EXAMPLE.COM', $user->email());
    }

    public function testWithWeirdSetters()
    {
        $data = '
            { "name": "Anton", "email": "anton@example.com" }
        ';
        $configuration = JsonMapperConfiguration::default()
            ->withPropertySetters(User::class, function ($property) {
                if ($property == 'address') {
                    return 'setAddressOrDefault';
                } else {
                    return JsonMapper::SETTER_IGNORE;
                }
            });

        $user = JsonMapper::map(_class(User::class), json_decode($data), $configuration);

        $this->assertEquals('Anton', $user->name());
        $this->assertEquals('anton@example.com', $user->email());
        $this->assertEquals('unknown', $user->address()->city());
    }

    public function testWithSetter()
    {
        $data = '
            { "name": "Anton", "email": "anton@example.com" }
        ';
        $configuration = JsonMapperConfiguration::default()
            ->withPropertySetter(User::class, 'name', 'setName');

        $user = JsonMapper::map(_class(User::class), json_decode($data), $configuration);

        $this->assertEquals('ANTON', $user->name());
        $this->assertEquals('anton@example.com', $user->email());
    }

    public function testWithConverter()
    {
        $data = '
            { "name": "Anton", "email": "anton@example.com", "preferences": "{\"a\": \"1\"}" }
        ';
        $configuration = JsonMapperConfiguration::default()
            ->withConverter(User::class, 'preferences', function (string $json) {
                return _map(_string(), _string())->cast(json_decode($json));
            });

        $user = JsonMapper::map(_class(User::class), json_decode($data), $configuration);

        $this->assertEquals('Anton', $user->name());
        $this->assertCount(1, $user->preferences());
        $this->assertEquals(1, $user->preferences()['a']);
    }
}
