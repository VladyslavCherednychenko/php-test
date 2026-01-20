<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserProfileRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Table(name: 'user_profile')]
#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'validation.assert.unique')]
class UserProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'user_profile_id', type: 'integer')]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'profile')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['user:read'])]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Assert\NotBlank(message: 'validation.assert.not_blank')]
    #[Groups(['user:read'])]
    private ?string $username = null;

    #[ORM\Column(name: 'first_name', type: 'string', length: 50, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $firstName = null;

    #[ORM\Column(name: 'last_name', type: 'string', length: 50, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $lastName = null;

    #[ORM\Column(name: 'profile_image', type: 'string', length: 100, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $profileImage = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $bio = null;

    /* Getters and Setters */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }
    public function setProfileImage(?string $profileImage): self
    {
        $this->profileImage = $profileImage;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }
    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }
}
