<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\FallbackUserLoader;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

/**
 * Access token handler for SAML
 */
class AccessTokenHandler implements AccessTokenHandlerInterface
{
    // Constructor
    public function __construct(
        protected RequestStack $requestStack,
        protected Saml2 $saml2,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $request = $this->requestStack->getCurrentRequest();
        try {
            $this->saml2->authenticate();
            if ($this->saml2->isConnected()) {
                $user = $this->saml2->getUser(); // AccessTokenUser
                return new UserBadge(
                    $user->getUserIdentifier(),
                    fn (string $userIdentifier) => $user // User loader
                );
            }
        } catch (Throwable $e) {
            throw new BadCredentialsException($e->getMessage());
        }
    }
}
