<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\LoginLink\Exception\InvalidLoginLinkAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

/**
 * Authentication failure handler
 */
class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{

    public function __construct(protected Language $language)
    {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Save exception in request attribute for AuthenticationUtils::getLastAuthenticationError()
        $request->attributes->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        // Log error
        LogError($exception);

        // Get route name
        $routeName = $request->attributes->get('_route', '');

        // Login page
        if (in_array($routeName, ['login', 'login1fa', 'login2fa', 'loginldap'])) {
            $failureMessage = $this->language->phrase('InvalidUidPwd');
            if ($exception instanceof CustomUserMessageAuthenticationException) {
                $failureMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
            } elseif ($exception instanceof InvalidCsrfTokenException) {
                $failureMessage = $this->language->phrase('InvalidCsrfToken');
            }

            // Captcha enabled and 2FA
            if (Config('USE_PHPCAPTCHA_FOR_LOGIN') && $routeName == 'login1fa' && ($exception->getMessageData()['captcha'] ?? false)) {
                $request->getSession()->getFlashBag()->add('danger', $failureMessage); // Set up failure message
                return new JsonResponse(['errorUrl' => UrlFor('login')]); // Reload login page to refresh captcha
            // 2FA always returns JSON
            } elseif ($routeName == 'login2fa') {
                return new JsonResponse(['success' => false, 'error' => $failureMessage]);
            // If JSON response expected
            } elseif (IsJsonResponse()) {
                return new JsonResponse(['error' => $failureMessage]);
            } else {
                $request->getSession()->getFlashBag()->add('danger', $failureMessage); // Set up failure message
                return new RedirectResponse(UrlFor('login')); // Go to login page
            }
        // Login check (for login link)
        } elseif ($routeName == 'login_check') {
            if ($exception instanceof InvalidLoginLinkAuthenticationException) {
                $request->getSession()->getFlashBag()->add('danger', $this->language->phrase('LoginLinkFailure')); // Set up failure message
            }
            return new RedirectResponse(UrlFor('login')); // Go to login page
        }

        // Other pages
        $failureMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
        $request->getSession()->getFlashBag()->add('danger', $failureMessage); // Set up failure message
        if (
            IsJsonResponse() // JSON response expected
            || IsModal() // Modal
            && !($routeName == 'login' && Config('USE_MODAL_LOGIN')) // Not modal login
        ) {
            return in_array($routeName, ['login', 'login1fa', 'loginldap']) && $exception instanceof BadCredentialsException
                ? new JsonResponse(['error' => $failureMessage])
                : new JsonResponse(['url' => UrlFor('login')]);
        }

        // Redirect to login page
        return new RedirectResponse(UrlFor('login'));
    }
}
