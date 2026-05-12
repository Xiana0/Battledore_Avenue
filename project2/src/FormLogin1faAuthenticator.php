<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class FormLogin1faAuthenticator extends AbstractLoginFormAuthenticator
{

    public function __construct(
        protected UserProviderInterface $userProvider,
        protected AuthenticationSuccessHandlerInterface $successHandler,
        protected AuthenticationFailureHandlerInterface $failureHandler,
        protected LoginListener $loginListener,
        protected Language $language,
        protected UserProfile $profile,
        protected array $options,
    ) {
        $this->options = array_merge([
            'username_parameter' => '_username',
            'password_parameter' => '_password',
            'check_path' => '/login_check',
            'post_only' => true,
            'form_only' => false,
            'enable_csrf' => false,
            'csrf_parameter' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
        ], $options);
    }

    protected function getLoginUrl(Request $request): string
    {
        return UrlFor('login1fa');
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);
        if (IsEmpty($credentials['username']) || IsEmpty($credentials['password']) && !Config('OTP_ONLY')) { // Check empty username / password
            throw new CustomUserMessageAuthenticationException($this->language->phrase('InvalidUidPwd'));
        }
        if (Config('USE_PHPCAPTCHA_FOR_LOGIN')) { // Validate captcha for login
            $captcha = Captcha();
            $captcha->Response = Post($captcha->getElementName());
            $sessionName = AddTabId($captcha->getSessionName('login'));
            if ($captcha->Response != $request->getSession()->get($sessionName)) {
                throw new CustomUserMessageAuthenticationException($this->language->phrase(IsEmpty($captcha->Response) ? 'EnterValidateCode' : 'IncorrectValidationCode'), ['captcha' => true]);
            }
        }
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $credentials['username']);
        $userBadge = new UserBadge(
            $credentials['username'],
            $this->userProvider->loadUserByIdentifier(...)
        );
        if (Config('OTP_ONLY')) { // Check user name only
            $listener = $this->loginListener;
            $checker = function($credentials, $user) use ($listener) {
                if ($user == null) {
                    return false; // User not found
                }
                return true; // User found, no password check
            };
            $passport = new Passport($userBadge, new CustomCredentials($checker, $credentials));
        } else {
            $passport =  new Passport($userBadge, new PasswordCredentials($credentials['password']));
        }
        if ($this->options['enable_csrf']) {
            $passport->addBadge(new CsrfTokenBadge($this->options['csrf_token_id'], $credentials['csrf_token']));
        }
        return $passport;
    }

    protected function getCredentials(Request $request): array
    {
        $credentials = [
            'csrf_token' => ParameterBagUtils::getParameterBagValue($request->request, $this->options['csrf_parameter']),
            'username' => ParameterBagUtils::getParameterBagValue($request->request, 'username'),
            'password' => ParameterBagUtils::getParameterBagValue($request->request, 'password') ?? '',
        ];
        return $credentials;
    }

    protected function useTwoFactorAuthentication(UserInterface $user): bool
    {
        return $this->profile->setUser($user)->get2FAEnabled();
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $user = $passport->getUser();
        if ($this->useTwoFactorAuthentication($user)) {
            $token = new TwoFactorAuthenticatingToken($user, $firewallName, $user->getRoles());
            return $token;
        } else {
            return new UsernamePasswordToken($user, $firewallName, $user->getRoles());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($this->useTwoFactorAuthentication($token->getUser())) {
            $request->getSession()->set(SESSION_STATUS, 'loggingin2fa');
            return new JsonResponse(['url' => UrlFor('login2fa')]); // Go to 2nd factor authentication
        } else {
            return $this->successHandler->onAuthenticationSuccess($request, $token);
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if (IsJsonResponse()) {
            $error = $exception instanceof BadCredentialsException ? $this->language->phrase('InvalidUidPwd') : $exception->getMessage();
            return new JsonResponse(['success' => false, 'error' => $error]);
        } else {
            return $this->failureHandler->onAuthenticationFailure($request, $exception);
        }
    }
}
