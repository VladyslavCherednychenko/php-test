<?php

namespace App\Dto;

use App\Entity\User;
use App\Exception\DtoValidationException;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Map(target: User::class)]
readonly class UserRegistrationDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'registration.email.assert.not_blank')]
        #[Assert\Email(message: 'registration.email.assert.email')]
        public string $email,

        #[Assert\NotBlank(message: 'registration.password.assert.not_blank')]
        #[Assert\PasswordStrength(message: 'registration.password.assert.password_strength')]
        public string $password,
    ) {}

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        $validator = $context->getValidator();
        $properties = ['email', 'password'];
        $errorMap = [];

        foreach ($properties as $property) {
            $violations = $validator->validateProperty($this, $property);
            
            foreach ($violations as $violation) {
                $errorMap[$property][] = $violation->getMessage();
            }
        }

        if (!empty($errorMap)) {
            throw new DtoValidationException($errorMap, json_encode($errorMap));
        }
    }
}
