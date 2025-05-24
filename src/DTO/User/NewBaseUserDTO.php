<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\Interface\DTO\User\CreateUserDTOInterface;

readonly class NewBaseUserDTO implements CreateUserDTOInterface 
{
    public function __construct(
        private string $email,
        private string $first_name,
        private string $last_name,
        private string $password,
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}

