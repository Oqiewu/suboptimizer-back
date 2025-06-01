<?php

declare(strict_types=1);

namespace App\DTO\User\Create;

use App\DTO\User\AbstractCreateUserDTO;

readonly final class BaseUserDTO extends AbstractCreateUserDTO
{
    public function __construct(
        string $email,
        string $firstName,
        string $lastName,
        string $password,
    ) {
        parent::__construct($email, $firstName, $lastName, $password);
    }
}

