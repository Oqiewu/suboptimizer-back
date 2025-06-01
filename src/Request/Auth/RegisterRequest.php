<?php

declare(strict_types=1);

namespace App\Request\Auth;

use App\DTO\User\NewBaseUserDTO;
use App\Interface\Request\RegisterRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterRequest implements RegisterRequestInterface
{
    public function __construct(
        #[Assert\Email]
        private string $email,
        private string $first_name,
        private string $last_name,
        #[Assert\Length(min: 6)]
        private string $password,
        private bool $is_remember = false,
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function isRemember(): bool
    {
        return $this->is_remember;
    }

    public function toDTO(): NewBaseUserDTO
    {
        return new NewBaseUserDTO(
            email: $this->email,
            first_name: $this->first_name,
            last_name: $this->last_name,
            password: $this->password
        );
    }
}
