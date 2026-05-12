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
 * Admins controller
 */
class AdminsController extends BaseController
{
    // list
    #[Route('/AdminsList', methods: ['GET', 'POST', 'OPTIONS'], name: 'list.admins')]
    public function list(Request $request, AdminsList $page): Response
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
    #[Route('/AdminsAdd/{id:admin?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'add.admins')]
    public function add(Request $request, AdminsAdd $page, ?Entity\Admin $admin = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($admin) {
            $page->CurrentRecord = $admin;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // view
    #[Route('/AdminsView/{id:admin?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'view.admins')]
    public function view(Request $request, AdminsView $page, ?Entity\Admin $admin = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($admin) {
            $page->CurrentRecord = $admin;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // edit
    #[Route('/AdminsEdit/{id:admin?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'edit.admins')]
    public function edit(Request $request, AdminsEdit $page, ?Entity\Admin $admin = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($admin) {
            $page->CurrentRecord = $admin;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // delete
    #[Route('/AdminsDelete/{id:admin?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'delete.admins')]
    public function delete(Request $request, AdminsDelete $page, ?Entity\Admin $admin = null): Response
    {
        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($admin) {
            $page->CurrentRecord = $admin;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }
}
