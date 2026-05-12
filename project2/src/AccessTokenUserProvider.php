<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use ReflectionClass;
use LogicException;

/**
 * Access token user provider (for refreshing user)
 */
class AccessTokenUserProvider implements UserProviderInterface
{
    /**
     * Load user by identifier (not used, a user loader is passed to UserBadge in AccessTokenHandler)
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $ex = new UserNotFoundException('Not supported.');
        $ex->setUserIdentifier($identifier);
        throw $ex; // Skip to other user provider
    }

    /**
     * Refresh the user after being reloaded from the session
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AccessTokenUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }
        return $user;
    }

    /**
     * Tell Symfony to use this provider for this User class
     */
    public function supportsClass(string $class): bool
    {
        return AccessTokenUser::class === $class;
    }
}
