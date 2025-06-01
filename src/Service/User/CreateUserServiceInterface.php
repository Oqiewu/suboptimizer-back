<?php

declare(strict_types=1);

namespace App\Service\User;

use App\DTO\User\AbstractCreateUserDTO;
use Symfony\Component\Security\Core\User\UserInterface;

interface CreateUserServiceInterface
{
    public function createUser(AbstractCreateUserDTO $createUserDTO): UserInterface;
}
