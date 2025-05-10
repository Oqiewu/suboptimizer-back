<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RefreshTokenRepository;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table(name: 'refresh_token')]
class RefreshToken
{
    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $token;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $validAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User', cascade: ['remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;
    public function __construct(User $user, string $token, \DateTime $validAt)
    {
        $this->user = $user;
        $this->token = $token;
        $this->validAt = $validAt;
    }

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

    public function getUser(): User
    {
        return $this->user;
    }
}
