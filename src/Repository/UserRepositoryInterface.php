<?php

declare(strict_types=1);

namespace App\Repository;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Persistence\ObjectRepository;

interface UserRepositoryInterface extends ObjectRepository
{
    public function create(UserInterface $user): void;
    public function update(UserInterface $user): void;
    public function delete(UserInterface $user): void;
}