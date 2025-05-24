<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use App\DTO\User\NewBaseUserDTO;
use Symfony\Component\Validator\Constraints as Assert;
use App\Interface\DTO\User\CreateUserDTOInterface;

class RegisterRequestDTO implements CreateUserDTOInterface
{
    #[Assert\Valid]
    public NewBaseUserDTO $userData;

    #[Assert\Type("bool")]
    private bool $is_remember;

    public function __construct(
        NewBaseUserDTO $userData,
        bool $is_remember = false,
    ) {
        $this->userData = $userData;
        $this->is_remember = $is_remember;
    }

    public function getEmail(): string
    {
        return $this->userData->getEmail();
    }

    public function getFirstName(): string
    {
        return $this->userData->getFirstName();
    }

    public function getLastName(): string
    {
        return $this->userData->getLastName();
    }

    public function getPassword(): string
    {
        return $this->userData->getPassword();
    }

    public function isRemember(): bool
    {
        return $this->is_remember;
    }
}

