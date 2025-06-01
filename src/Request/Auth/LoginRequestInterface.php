<?php

declare(strict_types=1);

namespace App\Request\Auth;

interface LoginRequestInterface
{
    public function getEmail(): string;
    public function getPassword(): string;
    public function isRemember(): bool;
}