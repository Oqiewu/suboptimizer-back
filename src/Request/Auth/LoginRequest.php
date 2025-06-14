<?php

declare(strict_types=1);

namespace App\Request\Auth;

use App\Interface\Request\LoginRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

readonly class LoginRequest implements LoginRequestInterface
{
    public function __construct(
        #[Assert\Email]
        private string $email,
        #[Assert\Length(min: 6)]
        private string $password,
        private bool $is_remember = false
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function isRemember(): bool
    {
        return $this->is_remember;
    }
}
