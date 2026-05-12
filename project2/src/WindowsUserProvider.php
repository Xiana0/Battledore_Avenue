<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class WindowsUserProvider implements UserProviderInterface
{
    /**
     * Load user by identifier
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!$identifier || $identifier !== CurrentWindowsUser()) {
            throw new UserNotFoundException(sprintf(Language()->phrase("UserNotFound"), $identifier));
        }
        return new WindowsUser($identifier);
    }

    /**
     * Refresh the user after being reloaded from the session
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user->getUserIdentifier() !== CurrentWindowsUser()) {
            throw new UserNotFoundException(sprintf(Language()->phrase("UserNotFound"), $user->getUserIdentifier()));
        }
        return new WindowsUser(CurrentWindowsUser());
    }

    /**
     * Tell Symfony to use this provider for this User class
     */
    public function supportsClass(string $class): bool
    {
        return WindowsUser::class === $class;
    }
}
