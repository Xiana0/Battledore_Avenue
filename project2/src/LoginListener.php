<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Http\Event\AuthenticationTokenCreatedEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\TokenDeauthenticatedEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PreAuthenticatedUserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Bundle\SecurityBundle\Security;
use Exception;

class LoginListener implements EventSubscriberInterface
{

    public function __construct(
        protected RequestStack $requestStack,
        protected UserProfile $profile,
        protected Language $language,
        protected AdvancedSecurity $security,
        protected Security $symfonySecurity,
    ) {
    }

    /**
     * Priorities of listeners of CheckPassportEvent: (The higher the priority, the earlier a listener is executed.)
     * LoginThrottlingListener (2080)
     * security.listener.<firewall>.user_provider (2048)
     * UserProviderListener (1024)
     * CsrfProtectionListener (512) // Login
     * UserCheckerListener (256) (CheckPassportEvent and AuthenticationSuccessEvent)
     * CheckLdapCredentialsListener (144)
     * CheckCredentialsListener (0)
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => [
                ['onUserLogin', 164],
            ],
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
            LoginSuccessEvent::class => 'onLoginSuccess',
            // InteractiveLoginEvent::class => 'onInteractiveLogin',
        ];
    }

    public function onUserLogin(CheckPassportEvent $event): void
    {
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        $user = $event->getUser();
        $routeName = $request->attributes->get('_route', '');
        if (in_array($routeName, ['login_check', 'api_login_check', 'login', 'login1fa', 'login2fa'])) {
            $userIdentifier = $user->getUserIdentifier();

            // Login user so CurrentUserID() returns value
            $this->security->login(); // Advanced Security also get user from symfony security
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();
        $request = $event->getRequest();
        $response = $event->getResponse();
        $exception = $event->getException();
        if ($exception instanceof InvalidCsrfTokenException && $response instanceof JsonResponse) {
            $failureMessage = $this->language->phrase('InvalidCsrfToken', true);
        } elseif ($exception instanceof TooManyLoginAttemptsAuthenticationException && $response instanceof JsonResponse) {
            $failureMessage = $this->language->phrase('ExceedMaxRetry', true);
        } else {
            $failureMessage = $exception->getMessage();
        }

        // If JSON response expected
        if (IsJsonResponse($response)) {
            $response = new JsonResponse(['success' => false, 'error' => $failureMessage]);
            $event->setResponse($response);
        } else {
            $request->getSession()->getFlashBag()->add('danger', $failureMessage); // Set up failure message
            $response = new RedirectResponse(UrlFor('login')); // Go to login page
            $event->setResponse($response);
        }
        $routeName = $request->attributes->get('_route', '');
        if (!$passport || !$passport->hasBadge(UserBadge::class)) {
            return; // Skip if no user badge
        }
        if (in_array($routeName, ['login_check', 'api_login_check', 'login', 'login1fa', 'login2fa'])) {
            try {
                $user = $passport->getUser();
                $userIdentifier = $user?->getUserIdentifier();
            } catch (Exception $e) {
                $user = null;
                $userIdentifier = $passport->getBadge(UserBadge::class)->getUserIdentifier();
            }
        }
    }

    /**
     * Handle interactive login and replace the user if needed
     */
    // public function onInteractiveLogin(InteractiveLoginEvent $event): void
    // {
    //     /** @var TokenInterface $token */
    //     $token = $event->getAuthenticationToken();

    //     /** @var UserInterface $currentUser */
    //     $user = $token->getUser();
    // }

    // User Logging In event
    public function userLoggingIn(string $userName, ?string $password): bool
    {
        // Enter your code here
        // To cancel, set return value to false
        return true;
    }

    // User Logged In event
    public function userLoggedIn(string $userName): void
    {
        //Log("User Logged In");
    }

    // User Login Error event
    public function userLoginError(string $userName, ?string $password): void
    {
        //Log("User Login Error");
    }
}
