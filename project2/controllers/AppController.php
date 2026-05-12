<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * App controller
 */
class AppController extends BaseController
{
    // Session and JWT refresher
    #[Route('/session', methods: ['GET', 'OPTIONS'], name: 'session')]
    public function session(Request $request): Response
    {
        return new Response();
    }

    // Index
    #[Route('/', methods: 'GET', name: 'index')]
    public function index(Request $request): Response
    {
        $url = 'AccessoriesList';
        if ($url === '') {
            foreach ($this->parameters->get('user.level.tables') as $t) {
                if (!empty($t[5])) {
                    $url = $t[5];
                    break;
                }
            }
        }
        if ($url == '') {
            throw new HttpException(401, DeniedMessage());
        }
        return new RedirectResponse($url, Config('REDIRECT_STATUS_CODE'));
    }
}
