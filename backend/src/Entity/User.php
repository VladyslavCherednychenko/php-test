<?php
namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'validation.assert.unique')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'user_id', type: 'integer')]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['user:read'])]
    #[Assert\NotBlank(message: 'validation.assert.not_blank')]
    #[Assert\Email(message: 'validation.assert.email')]
    #[Assert\Type('string', message: 'validation.assert.type_string')]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'validation.assert.not_blank')]
    #[Assert\PasswordStrength(message: 'validation.assert.password_strength')]
    #[Assert\Type('string', message: 'validation.assert.type_string')]
    private ?string $password = null;

    #[ORM\Column(enumType: UserRole::class, options: ['default' => 0])]
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
        $roles = ['ROLE_' . strtoupper($this->role->name)];
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    public function eraseCredentials(): void
    {}

    /* RELATIONS */
    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserProfile::class, cascade: ['persist', 'remove'])]
    #[Groups(['user:read'])]
    private ?UserProfile $profile = null;

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }
    public function setProfile(UserProfile $profile): self
    {
        $this->profile = $profile;
        if ($profile->getUser() !== $this) {
            $profile->setUser($this);
        }
        return $this;
    }
}
