<?php

declare(strict_types=1);

namespace App\Request\Auth\Register;

use App\DTO\User\Create\BaseUserDTO;
use App\Request\Auth\RegisterRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

readonly final class RegisterRequest implements RegisterRequestInterface
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

    public function toDTO(): BaseUserDTO
    {
        return new BaseUserDTO(
            email: $this->email,
            firstName: $this->first_name,
            lastName: $this->last_name,
            password: $this->password
        );
    }
}
