<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as BaseVoter;

/**
 * Permission Voter
 */
class PermissionVoter extends BaseVoter
{

    public function __construct(
        protected RequestStack $requestStack,
        protected AdvancedSecurity $security,
        protected UserProfile $profile,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $routeName = $request->attributes->get('_route', '');
        return true;
    }
}
