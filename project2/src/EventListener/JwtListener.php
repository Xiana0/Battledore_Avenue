<?php

namespace PHPMaker2026\Project1\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use PHPMaker2026\Project1\UserProfile;
use PHPMaker2026\Project1\AdvancedSecurity;
use PHPMaker2026\Project1\Language;
use function PHPMaker2026\Project1\Config;

#[AsEventListener(
    event: Events::JWT_CREATED,
    method: 'onJWTCreated'
)]
#[AsEventListener(
    event: Events::JWT_AUTHENTICATED,
    method: 'onJWTAuthenticated'
)]
#[AsEventListener(
    event: Events::AUTHENTICATION_FAILURE,
    method: 'onAuthenticationFailure'
)]
class JwtListener
{

    public function __construct(
        protected RequestStack $requestStack,
        protected UserProfile $profile,
        protected AdvancedSecurity $security,
        protected Language $language,
    ) {
    }

    /**
     * Get value from request (query or request)
     *
     * @param Request $request
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get(Request $request, string $key, mixed $default = null): mixed
    {
        if ($request->query->has($key)) {
            return $request->query->all()[$key];
        }
        if ($request->request->has($key)) {
            return $request->request->all()[$key];
        }
        return $default;
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();

        // Ensure user profile is loaded on the first (login) request,
        // since onJWTAuthenticated (which normally loads it) is not called here.
        $user = $event->getUser();
        $this->profile->setUser($user);
        $this->security->loginUser($user);

        // Add user data to the JWT payload
        $payload = array_merge($payload, $this->security->getJwtPayload());

        // Get expiry time and permission
        $request = $this->requestStack->getCurrentRequest();
        $expire = intval($this->get($request, Config('API_LOGIN_EXPIRE'))); // Get expiry time in hours
        $permission = intval($this->get($request, Config('API_LOGIN_PERMISSION'))); // Get permission
        $exp = $expire ? time() + $expire * 60 * 60 : 0;
        if ($expire && $minExpiry) {
            $payload['userPermission'] = $permission;
            $payload['exp'] = $exp;
        }
        $event->setData($payload);
    }

    public function onJWTAuthenticated(JWTAuthenticatedEvent $event)
    {
        $token = $event->getToken();
        $user = $token->getUser();
        $this->profile->setUser($user);
        $this->security->loginUser($user);
        $payload = $event->getPayload();
        $this->security->setUserPermissions($payload['userPermission'] ?? 0); // Set user permissions
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
    }
}
