<?php

declare(strict_types=1);

namespace App\Request\Auth;

use App\DTO\User\AbstractCreateUserDTO;

interface RegisterRequestInterface
{
    public function getEmail(): string;
    public function getPassword(): string;
    public function getFirstName(): string;
    public function getLastName(): string;
    public function isRemember(): bool;
    public function toDTO(): AbstractCreateUserDTO;
}