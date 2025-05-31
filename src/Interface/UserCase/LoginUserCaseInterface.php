<?php

namespace App\Interface\UserCase;

use App\Interface\Request\LoginRequestInterface;
use App\Interface\Response\ResponseInterface;

interface LoginUserCaseInterface
{
    public function authenticate(LoginRequestInterface $loginRequest): ResponseInterface;
}