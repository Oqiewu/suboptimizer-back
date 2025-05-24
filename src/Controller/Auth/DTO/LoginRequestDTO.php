<?php

declare(strict_types=1);

namespace App\Controller\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class LoginRequestDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 6)]
        public string $password,

        #[Assert\Type("bool")]
        public bool $is_remember = false
    ) {}
}