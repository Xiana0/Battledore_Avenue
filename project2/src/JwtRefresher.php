<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWT refresher
 */
class JwtRefresher
{

    public function __invoke(?UserInterface $user): Response
    {
        $token = GetJwtToken();
        $response = new JsonResponse(['token' => $token]);
        $cookie = GetJwtCookie($token);
        $response->headers->setCookie($cookie);
        return $response;
    }
}
