<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as BaseVoter;

class SwitchUserVoter extends BaseVoter
{

    protected function supports(string $attribute, mixed $subject): bool
    {
        // If the attribute is not that we support, return false
        if ($attribute != Config('SECURITY.firewalls.main.switch_user.role')) {
            return false;
        }

        // Only vote on database user
        if (!IsEntityUser($subject)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (IsAdmin()) {
            return true;
        }

        // Current user
        $user = $token->getUser();

        // Current user must be logged in so the user IDs are loaded
        if (!$user) {
            $vote?->addReason('No current user.');
            return false;
        }

        // New user to be switched to
        $newUser = $subject;

        // The two users should not be the same user
        if ($user->getUserIdentifier() == $newUser->getUserIdentifier()) {
            $vote?->addReason('The new user should not be the same user as the current user.');
            return false;
        }

        // Make sure the current user is a parent user of the new user
        return in_array($newUser->get(Config("USER_ID_FIELD_NAME")), Security()->UserIDs);
    }
}
