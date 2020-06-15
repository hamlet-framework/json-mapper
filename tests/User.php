<?php

namespace Hamlet\JsonMapper;

class User
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var Address|null
     */
    private $address;

    /**
     * @var array[]|null
     * @psalm-var array<string,string>|null
     */
    private $preferences;

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function email()
    {
        return $this->email;
    }

    public function address()
    {
        return $this->address;
    }

    public function setName(string $name)
    {
        $this->name = strtoupper($name);
    }

    public function setEmail(string $email)
    {
        $this->email = strtoupper($email);
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function setPreferences($preferences)
    {
        $this->preferences = $preferences;
    }

    public function setAddressOrDefault($address)
    {
        $this->address = ($address ?? new Address('unknown'));
    }

    public function preferences(): array
    {
        return $this->preferences ?? [];
    }
}
