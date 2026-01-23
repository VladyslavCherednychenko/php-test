<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProfileDto
{
    #[Assert\Length(min: null, max: 50, maxMessage: "validation.assert.length.max_50")]
    public string $username;

    #[Assert\Length(min: null, max: 50, maxMessage: "validation.assert.length.max_50")]
    public ?string $firstName = null;

    #[Assert\Length(min: null, max: 50, maxMessage: "validation.assert.length.max_50")]
    public ?string $lastName = null;

    #[Assert\Length(min: null, max: 255, maxMessage: "validation.assert.length.max_255")]
    public ?string $bio = null;
}
