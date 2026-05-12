<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $routeName = $request->attributes->get('_route', '');
        $redirectRouteName = $request->attributes->get('redirect') ?? 'login';
        if ($authException) {
            $request->getSession()->getFlashBag()->add('danger', DeniedMessage()); // Set no permission
        }
        if (
            IsJsonResponse() // JSON response expected
            || IsModal() // Modal
            && !($routeName == 'login' && Config('USE_MODAL_LOGIN')) // Not modal login
        ) {
            return new JsonResponse(['url' => UrlFor($redirectRouteName)]);
        }

        // Redirect
        return new RedirectResponse(UrlFor($redirectRouteName), Config('REDIRECT_STATUS_CODE'));
    }
}
