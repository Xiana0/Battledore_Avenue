<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class OAuthUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    protected string $username;
    protected array $data = [];
    protected ?string $nickName = null;
    protected ?string $firstName = null;
    protected ?string $lastName = null;
    protected ?string $realName = null;
    protected ?string $email = null;
    protected ?string $profilePicture = null;

    /**
     * Constructor to initialize user with a username (identifier).
     *
     * @param string $username Unique identifier (usually email or username).
     */
    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * Returns the unique identifier for the user (e.g., email or username).
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * Returns the roles assigned to the user.
     *
     * @return array<int, string> List of role strings.
     */
    public function getRoles(): array
    {
        return ['ROLE_USER', 'ROLE_OAUTH_USER'];
    }

    /**
     * Returns null as password is not used for OAuth-authenticated users.
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * Sets the username (user identifier).
     *
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Returns the legacy username (alias of getUserIdentifier).
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * Erases temporary sensitive data.
     *
     * Deprecated as no credentials are stored for OAuth users.
     *
     * @return void
     */
    #[\Deprecated(since: 'symfony/security-core 7.3')]
    public function eraseCredentials(): void
    {
    }

    /**
     * Compares this user with another for equality.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function equals(UserInterface $user): bool
    {
        return $user->getUserIdentifier() === $this->username;
    }

    /**
     * Returns additional user data from OAuth provider.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Sets additional user data from OAuth provider.
     *
     * @param array<string, mixed> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Returns the user's nickname if available.
     *
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->nickName;
    }

    /**
     * Sets the user's nickname.
     *
     * @param string|null $nickName
     */
    public function setNickname(?string $nickName): void
    {
        $this->nickName = $nickName;
    }

    /**
     * Returns the user's first name if available.
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Sets the user's first name.
     *
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * Returns the user's last name if available.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Sets the user's last name.
     *
     * @param string|null $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * Returns the user's real name if available.
     *
     * @return string|null
     */
    public function getRealName(): ?string
    {
        return $this->realName;
    }

    /**
     * Sets the user's real name.
     *
     * @param string|null $realName
     */
    public function setRealName(?string $realName): void
    {
        $this->realName = $realName;
    }

    /**
     * Returns the user's email if available.
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets the user's email.
     *
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * Returns the user's profile picture URL if available.
     *
     * @return string|null
     */
    public function getProfilePicture(): ?string
    {
        return $this->data['profilepicture'] ?? null;
    }

    /**
     * Sets the user's profile picture URL.
     *
     * @param string|null $profilePicture
     */
    public function setProfilePicture(?string $profilePicture): void
    {
        $this->profilePicture = $profilePicture;
    }
}
