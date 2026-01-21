<?php
namespace App\Dto;

use App\Entity\User;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: User::class)]
readonly class AuthDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'validation.assert.not_blank')]
        #[Assert\Email(message: 'validation.assert.email')]
        #[Assert\Type('string', message: 'validation.assert.type_string')]
        public string $email,

        #[Assert\NotBlank(message: 'validation.assert.not_blank')]
        #[Assert\Type('string', message: 'validation.assert.type_string')]
        public string $password,
    ) {}
}
