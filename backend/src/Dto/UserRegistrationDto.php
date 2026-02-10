<?php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: User::class)]
readonly class UserRegistrationDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'validation.assert.not_blank')]
        #[Assert\Email(message: 'validation.assert.email')]
        #[Assert\Type('string', message: 'validation.assert.type_string')]
        public string $email,

        #[Assert\NotBlank(message: 'validation.assert.not_blank')]
        #[Assert\PasswordStrength(message: 'validation.assert.password_strength')]
        #[Assert\Type('string', message: 'validation.assert.type_string')]
        public string $password,

        #[Assert\Type('boolean', message: 'validation.assert.type_bool')]
        public bool $rememberMe = false,
    ) {}
}
