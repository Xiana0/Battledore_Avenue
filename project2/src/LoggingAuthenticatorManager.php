<?php

namespace PHPMaker2026\Project1;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManagerInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Debug\TraceableAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logging Authenticator Manager
 */
class LoggingAuthenticatorManager implements AuthenticatorManagerInterface, UserAuthenticatorInterface
{

    public function __construct(
        protected AuthenticatorManagerInterface $inner,
        protected LoggerInterface $logger
    ) {}

    /**
     * Simply delegate authenticateRequest() to the inner manager
     */
    public function authenticateRequest(Request $request): ?Response
    {
        return $this->inner->authenticateRequest($request);
    }

    /**
     * Delegate support check to the inner manager and log skipped/supported authenticators
     */
    public function supports(Request $request): ?bool
    {
        $result = $this->inner->supports($request);
        $skippedAuthenticators = $request->attributes->get('_security_skipped_authenticators', []);
        $authenticators = $request->attributes->get('_security_authenticators', []);
        foreach ($skippedAuthenticators as $authenticator) {
            $class = $authenticator instanceof TraceableAuthenticator
                ? $authenticator->getAuthenticator()::class
                : $authenticator::class;
            $this->logger?->debug('Authenticator skipped for this request', [
                'authenticator_class' => $class,
                'request_path' => $request->getPathInfo(),
            ]);
        }
        foreach ($authenticators as $authenticator) {
            $class = $authenticator instanceof TraceableAuthenticator
                ? $authenticator->getAuthenticator()::class
                : $authenticator::class;
            $this->logger?->debug('Authenticator will be executed for this request', [
                'authenticator_class' => $class,
                'request_path' => $request->getPathInfo(),
            ]);
        }
        return $result;
    }

    /**
     * Delegate manual user authentication to the inner manager
     */
    public function authenticateUser(
        UserInterface $user,
        AuthenticatorInterface $authenticator,
        Request $request,
        array $badges = [],
        array $attributes = []
    ): ?Response {
        // $attributes = 4 < \func_num_args() ? \func_get_arg(4) : [];
        return $this->inner->authenticateUser($user, $authenticator, $request, $badges, $attributes);
    }
}
