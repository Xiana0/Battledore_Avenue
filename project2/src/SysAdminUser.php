<?php

namespace PHPMaker2026\Project1;

/**
 * Super admin user
 */
class SysAdminUser implements AdvancedUserInterface
{

    public function getRoles(): array
    {
        return ['ROLE_SUPER_ADMIN'];
    }

    public function getUserIdentifier(): string
    {
        return Config('ADMIN_USER_NAME');
    }

    public function isEnabled(): bool
    {
        return true;
    }

    #[\Deprecated(since: 'symfony/security-core 7.3')]
    public function eraseCredentials(): void
    {
    }

    public function userName(): string
    {
        return $this->getUserIdentifier();
    }

    public function userId(): mixed
    {
        return AdvancedSecurity::ADMIN_USER_ID;
    }

    public function parentUserId(): mixed
    {
        return null;
    }

    public function userLevel(): int|string
    {
        return AdvancedSecurity::ADMIN_USER_LEVEL_ID;
    }
}
