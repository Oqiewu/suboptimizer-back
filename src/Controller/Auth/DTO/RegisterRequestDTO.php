<?php

namespace App\Controller\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequestDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    public string $first_name;

    #[Assert\NotBlank]
    public string $last_name;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public string $password;

    #[Assert\Type("bool")]
    public bool $is_remember = false;
}