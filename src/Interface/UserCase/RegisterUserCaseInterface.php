<?php

declare(strict_types=1);

namespace App\Interface\UserCase;

use App\Interface\Request\RegisterRequestInterface;

interface RegisterUserCaseInterface
{
    public function register(RegisterRequestInterface $registerRequest): array;
}