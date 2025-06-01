<?php

declare(strict_types=1);

namespace App\Interface\Request;

use App\Interface\DTO\User\CreateUserDTOInterface;

interface RegisterRequestInterface
{
    public function getEmail(): string;
    public function getPassword(): string;
    public function getFirstName(): string;
    public function getLastName(): string;
    public function isRemember(): bool;
    public function toDTO(): CreateUserDTOInterface;
}