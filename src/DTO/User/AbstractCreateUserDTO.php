<?php

declare(strict_types=1);

namespace App\DTO\User;

abstract readonly class AbstractCreateUserDTO
{
    public function __construct(
        protected string $email,
        protected string $firstName,
        protected string $lastName,
        protected string $password,
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    public function getLastName(): string
    {
        return $this->lastName;
    }
    public function getPassword(): string
    {
        return $this->password;
    }
}
