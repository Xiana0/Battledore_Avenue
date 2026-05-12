<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\AuthenticationTokenCreatedEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\TokenDeauthenticatedEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AppEventSubscriber implements EventSubscriberInterface
{
    // Constructor
    public function __construct(
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
     * Priorities of listeners of KernelEvents::REQUEST:
     * WebProcessor (4096)
     * DebugHandlersListener (2048)
     * ValidateRequestListener (256)
     * Symfony\Bridge\Doctrine\Middleware\IdleConnection\Listener (192) // Before session listeners since they could use the DB
     * AbstractSessionListener (128)
     * AddRequestFormatsListener (100)
     * LocaleListener::setDefaultLocale (100)
     * FragmentListener (48)
     * RouterListener (32) // Route name is known
     * LocaleListener (16)
     * LocaleAwareListener (15)
     * FirewallListener (8)
     * SwitchUserListener (0)
     * RouteProcessor::addRouteData (1)
     * LogoutListener (-127)
     * AccessListener (-255)
     *
     * Priorities of listeners of KernelEvents::CONTROLLER_ARGUMENTS
     * IsCsrfTokenValidAttributeListener (25)
     * IsGrantedAttributeListener (20) (Use AuthorizationCheckerInterface isGranted() method)
     * CacheAttributeListener (10)
     * RequestPayloadValueResolver (0)
     * ErrorListener (0)
     *
     * Priorities of listeners of KernelEvents::RESPONSE:
     * AssetMapperDevServerSubscriber (2048)
     * RequestDataCollector (0)
     * ResponseListener (0)
     * SurrogateListener (0)
     * CacheAttributeListener (-10)
     * ProfilerListener (-100)
     * ErrorListener (-128)
     * WebDebugToolbarListener (-128)
     * DisallowRobotsIndexingListener (-255)
     * AbstractSessionListener (-1000)
     *
     * Priorities of listeners of KernelEvents::FINISH_REQUEST:
     * FirewallListener (0)
     * RouterListener (0)
     * LocaleListener (0)
     * LocaleAwareListener (-15)
     *
     * Priorities of listeners of LoginSuccessEvent:
     * SessionStrategyListener (0)
     * PasswordMigratingListener (0)
     * LoginThrottlingListener (0)
     * CheckRememberMeConditionsListener (-32) - Enable RememberMeBadge
     * RememberMeListener (-64) - Create cookie
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationTokenCreatedEvent::class => 'onAuthenticationTokenCreated',
            LogoutEvent::class => 'onLogout',
            KernelEvents::REQUEST => [
                ['onSessionCreated', 120], // After SessionListener (128)
                ['onKernelRequest', 4], // After FirewallListener (8)
            ],
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onAuthenticationTokenCreated(AuthenticationTokenCreatedEvent $event): void
    {
        $token = $event->getAuthenticatedToken();
        $user = $token->getUser();
        $this->profile->setUser($user);
        $userName = $identifier = $user->getUserIdentifier();

        // Call User_CustomValidate event
        if ($this->security->userCustomValidate($userName)) { // User_CustomValidate event returns true
            if ($userName != $identifier) {
                $identifier = $userName;
            }
            if (!IsSysAdminUser($user) && !IsEntityUser($user)) { // Profile might be changed, e.g. user ID/level
                $this->profile->saveToCache();
            }
        }

        // Try to find the entity user by identifier if authenticated by others, e.g. LDAP, OAuth, SAML, Windows, etc.
        if (
            !IsSysAdminUser($user) // Current user is not super admin
            && $identifier && ($entityUser = LoadUserByIdentifier($identifier)) // New entity user found
            && !$entityUser->isEqualTo($user) // New entity user != current user
        ) {
            $token = new UsernamePasswordToken($entityUser, 'main', $entityUser->getRoles());
            $event->setAuthenticatedToken($token); // Change token
            $this->profile->setUser($entityUser); // Set current user
            if (
                $this->profile->get2FAEnabled()
                && !IsLoggedIn()
                && !IsLoggingIn2FA()
            ) {
                $this->profile->setUserName($identifier)->loadFromStorage();
                Session(SESSION_STATUS, 'loggingin2fa');
                $token = new TwoFactorAuthenticatingToken($entityUser, 'main', $entityUser->getRoles());
                $event->setAuthenticatedToken($token); // Change token
            }
        }
    }

    public function onLogout(LogoutEvent $event): void
    {
    }

    public function onSessionCreated(RequestEvent $event): void
    {
        $this->language->setLanguage();
        $this->security->initialize();
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        $routeName = $request->attributes->get('_route', ''); // Get route name
        $token = $this->symfonySecurity->getToken();
        $user = $this->symfonySecurity->getUser();
        if ($user) {
            $this->profile->setUser($user);
            $this->security->login(); // Advanced Security also get user from symfony security

            // Set up user image
            if (IsEntityUser($user) && !IsEmpty(Config('USER_IMAGE_FIELD_NAME'))) {
                $imageField = UserTable()->Fields[Config('USER_IMAGE_FIELD_NAME')];
                if ($imageField->hasMethod('getUploadPath')) {
                    $imageField->UploadPath = $imageField->getUploadPath();
                }
                $image = GetFileImage($imageField, $user->get(Config('USER_IMAGE_FIELD_NAME')), Config('USER_IMAGE_SIZE'), Config('USER_IMAGE_SIZE'), Config('USER_IMAGE_CROP'));
                $this->profile->setUserImageBase64(base64_encode($image))->saveToStorage(); // Save as base64 encoded
            }
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Custom cookies
        $cookies = $request->attributes->get('cookies', []);
        foreach ($cookies as $cookie) {
            $response->headers->setCookie($cookie);
        }
    }
}
