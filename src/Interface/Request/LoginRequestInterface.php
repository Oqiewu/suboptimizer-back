<?php

namespace App\Interface\Request;

interface LoginRequestInterface
{
    public function getEmail(): string;
    public function getPassword(): string;
    public function isRemember(): bool;
}