<?php

declare(strict_types=1);

namespace App\Interface\Service\User;

use App\Entity\User;
use App\Interface\DTO\User\CreateUserDTOInterface;

interface CreateUserServiceInterface
{
    public function createUser(CreateUserDTOInterface $registerRequestDTO): User;
}
