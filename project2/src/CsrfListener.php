<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CsrfListener
{
    /**
     * Excluded route names or regex patterns (e.g. '/^api\./')
     *
     * @var string[]
     */
    public static array $excludedPatterns = [
        '/^api\./',
    ];

    public function __construct(
        protected CsrfTokenManagerInterface $csrfTokenManager,
        protected Language $language,
    ) {
    }

    public function __invoke(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        // Only protect unsafe HTTP methods
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            return;
        }
        $routeName = $request->attributes->get('_route', '');

        // Skip CSRF protection for excluded routes
        if ($this->isExcludedRoute($routeName)) {
            return;
        }

        // Only apply CSRF if explicitly required
        $csrfId = $request->request->get(Config("CSRF_TOKEN.id_key")); // Get "_csrf_id"
        if (
            in_array($csrfId, ['authenticate', 'logout']) // Handled by Symfony
            || !in_array($csrfId, Config('CSRF_PROTECTION.stateless_token_ids')) // Not 'submit' or other stateless token IDs
        ) {
            return;
        }

        // Get "_csrf_token", the value is the cookie name
        $tokenValue = $request->request->get(Config("CSRF_TOKEN.value_key"));
        if (!$tokenValue || !$this->csrfTokenManager->isTokenValid(new CsrfToken($csrfId, $tokenValue))) {
            throw new AccessDeniedHttpException($this->language->phrase('InvalidCsrfToken', true));
        }
    }

    protected function isExcludedRoute(string $routeName): bool
    {
        foreach (self::$excludedPatterns as $pattern) {
            if ($pattern === $routeName) {
                return true;
            }
            if (@preg_match($pattern, '') !== false && preg_match($pattern, $routeName)) {
                return true;
            }
        }
        return false;
    }
}
