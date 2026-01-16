<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\Index(columns: ['email'], name: 'IX_AppUser_Email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'user_id', type: 'integer')]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['user:read'])]
    #[Assert\NotBlank(message: "Email can not be empty")]
    #[Assert\Email(message: "Email format is invalid")]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'integer', enumType: UserRole::class, options: ['default' => 0])]
    #[Groups(['user:read'])]
    private UserRole $role = UserRole::USER;

    /* GETTERS & SETTERS */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getRoles(): array
    {
        return match ($this->role) {
            UserRole::USER => ['ROLE_USER'],
            UserRole::MODERATOR => ['ROLE_MODERATOR'],
            UserRole::ADMIN => ['ROLE_ADMIN'],
            default => ['ROLE_USER']
        };
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    public function eraseCredentials(): void {}
}
