<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * UserInterface implementation used by the Windows user provider
 *
 * Based on Symfony\Component\Security\Core\User\InMemoryUser
 */
class WindowsUser implements UserInterface, EquatableInterface, \Stringable
{
    private string $username;
    private bool $enabled;
    private array $roles;

    public function __construct(?string $username, array $roles = [], bool $enabled = true)
    {
        if ('' === $username || null === $username) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }
        $this->username = $username;
        $this->enabled = $enabled;
        $this->roles = array_unique(array_merge($roles, ['ROLE_USER']));
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Returns the identifier for this user (e.g. its username or email address).
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    #[\Deprecated(since: 'symfony/security-core 7.3')]
    public function eraseCredentials(): void
    {
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }
        $currentRoles = array_map('strval', (array) $this->getRoles());
        $newRoles = array_map('strval', (array) $user->getRoles());
        $rolesChanged = \count($currentRoles) !== \count($newRoles) || \count($currentRoles) !== \count(array_intersect($currentRoles, $newRoles));
        if ($rolesChanged) {
            return false;
        }
        if ($this->getUserIdentifier() !== $user->getUserIdentifier()) {
            return false;
        }
        if ($this->isEnabled() !== $user->isEnabled()) {
            return false;
        }
        return true;
    }
}
