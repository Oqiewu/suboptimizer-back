<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Token\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_token')]
class RefreshToken
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private readonly UserInterface $user,

        #[ORM\Column(type: 'string', length: 255, unique: true)]
        private string $token,

        #[ORM\Column(type: 'datetime')]
        private \DateTime $validAt,

        #[ORM\Id()]
        #[ORM\GeneratedValue()]
        #[ORM\Column(type: 'integer')]
        private ?int $id = null,
    ) {}
    public function getId(): int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getValidAt(): \DateTime
    {
        return $this->validAt;
    }

    public function setValidAt(\DateTime $validAt): void
    {
        $this->validAt = $validAt;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
