<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Authentication success handler
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{

    public function __construct(
        protected UserProfile $profile,
        protected AdvancedSecurity $security,
        protected Language $language,
        protected Security $symfonySecurity,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $routeName = $request->attributes->get('_route', '');

        // Login check (for login link)
        if ($routeName == 'login_check') {
            $session = $request->getSession();
            $flash = $session->getFlashBag();
            $user = $token->getUser();
            if (
                $request->query->get('action') == 'activate' // Activate user
                && Config('REGISTER_ACTIVATE')
                && !IsEmpty(Config('USER_ACTIVATED_FIELD_NAME'))
                && !ConvertToBool($user->get(Config('USER_ACTIVATED_FIELD_NAME')))
            ) {
                if ($this->security->activateUser($user)) { // Activate user
                    if (Config('REGISTER_AUTO_LOGIN')) {
                        if (Config('USE_TWO_FACTOR_AUTHENTICATION')) {
                            if ($this->profile->setUser($user)->get2FAEnabled()) {
                                $session->set(SESSION_STATUS, 'loggingin2fa');
                                $flash->add('success', $this->language->phrase('ActivateSuccess')); // Set up user activated message
                                return new RedirectResponse(UrlFor('login2fa')); // Go to two factor authentication
                            }
                        }
                        $flash->add('success', $this->language->phrase('ActivateSuccess')); // Set up user activated message
                    } else { // If not auto login after activation
                        $request->attributes->set('_success_message', 'ActivateSuccess'); // Set up user activated message in post session logout listener
                        $this->symfonySecurity->logout(false);
                        return new RedirectResponse(UrlFor('login')); // Go to login page
                    }
                } else {
                    $flash->add('danger', $this->language->phrase('ActivateFailed')); // Set activation failure message
                }
            } else {
                $flash->add('success', $this->language->phrase('LoginLinkSuccess')); // Set up success message
            }
            return new RedirectResponse(UrlFor('login')); // Go to login page to continue login
        } elseif (in_array($routeName, ['login1fa', 'login', 'loginldap'])) {
            $url = $this->security->lastUrl() ?? UrlFor('login');
            return IsJsonResponse() || IsModal() // If JSON response expected
                ? new JsonResponse(['url' => $url])
                : new RedirectResponse($url); // Go to login page
        }

        // External login
        foreach (array_keys(Config('EXTERNAL_LOGIN_PROVIDERS')) as $name) {
            if ($routeName == $name . '_login') { // Successful external login
                return new RedirectResponse(UrlFor('login')); // Go to login page
            }
        }

        // Return empty response => Continue to the original route
        return null;
    }
}
