<?php

declare(strict_types=1);

namespace App\Interface\UserCase;

use App\Interface\Request\RegisterRequestInterface;
use App\Interface\Response\ResponseInterface;

interface RegisterUserCaseInterface
{
    public function register(RegisterRequestInterface $registerRequest): ResponseInterface;
}
