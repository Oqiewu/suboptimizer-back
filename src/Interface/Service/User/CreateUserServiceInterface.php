<?php

declare(strict_types=1);

namespace App\Interface\Service\User;

use App\Interface\DTO\User\CreateUserDTOInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface CreateUserServiceInterface
{
    public function createUser(CreateUserDTOInterface $createUserDTO): UserInterface;
}
