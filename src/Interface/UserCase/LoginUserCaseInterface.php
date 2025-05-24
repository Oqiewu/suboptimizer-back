<?php

namespace App\Interface\UserCase;

use App\Interface\Request\LoginRequestInterface;

interface LoginUserCaseInterface
{
    public function authenticate(LoginRequestInterface $loginRequest): array;
}