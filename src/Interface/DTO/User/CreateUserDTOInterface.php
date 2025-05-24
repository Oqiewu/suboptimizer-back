<?php

declare(strict_types=1);

namespace App\Interface\DTO\User;

interface CreateUserDTOInterface
{
    public function getEmail(): string;
    public function getFirstName(): string;
    public function getLastName(): string;
    public function getPassword(): string;
}