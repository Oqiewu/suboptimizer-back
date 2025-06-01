<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\Persistence\ObjectRepository;

interface RefreshTokenRepositoryInterface extends ObjectRepository
{
    public function create(RefreshToken $refreshToken): void;
    public function update(RefreshToken $refreshToken): void;
    public function delete(RefreshToken $refreshToken): void;
}