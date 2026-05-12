<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use PHPMaker2026\Project1\Db\Entity;

/**
 * Users controller
 */
class UsersController extends BaseController
{
    // list
    #[Route('/UsersList', methods: ['GET', 'POST', 'OPTIONS'], name: 'list.users')]
    public function list(Request $request, UsersList $page): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Perform inline/grid actions
        if ($response = $page->action()) {
            return $response;
        }
        $page->TotalRecords = $page->listRecordCount();
        if (!$page->Records) {
            $page->Records = $page->loadRecords($page->StartRecord - 1, $page->DisplayRecords);
        }

        // Run page
        return $this->runPage($page);
    }

    // add
    #[Route('/UsersAdd/{id:user?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'add.users')]
    public function add(Request $request, UsersAdd $page, ?Entity\User $user = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($user) {
            $page->CurrentRecord = $user;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // view
    #[Route('/UsersView/{id:user?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'view.users')]
    public function view(Request $request, UsersView $page, ?Entity\User $user = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($user) {
            $page->CurrentRecord = $user;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // edit
    #[Route('/UsersEdit/{id:user?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'edit.users')]
    public function edit(Request $request, UsersEdit $page, ?Entity\User $user = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($user) {
            $page->CurrentRecord = $user;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // delete
    #[Route('/UsersDelete/{id:user?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'delete.users')]
    public function delete(Request $request, UsersDelete $page, ?Entity\User $user = null): Response
    {
        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($user) {
            $page->CurrentRecord = $user;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }
}
