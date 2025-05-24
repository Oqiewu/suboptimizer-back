<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\Interface\DTO\User\CreateUserDTOInterface;
use Symfony\Component\Validator\Constraints as Assert;

readonly class NewBaseUserDTO implements CreateUserDTOInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        private string $email,

        #[Assert\NotBlank]
        private string $first_name,

        #[Assert\NotBlank]
        private string $last_name,

        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        private string $password,
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
}

