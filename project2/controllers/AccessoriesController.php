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
 * Accessories controller
 */
class AccessoriesController extends BaseController
{
    // list
    #[Route('/AccessoriesList', methods: ['GET', 'POST', 'OPTIONS'], name: 'list.accessories')]
    public function list(Request $request, AccessoriesList $page): Response
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
    #[Route('/AccessoriesAdd/{id:accessory?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'add.accessories')]
    public function add(Request $request, AccessoriesAdd $page, ?Entity\Accessory $accessory = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($accessory) {
            $page->CurrentRecord = $accessory;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // view
    #[Route('/AccessoriesView/{id:accessory?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'view.accessories')]
    public function view(Request $request, AccessoriesView $page, ?Entity\Accessory $accessory = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($accessory) {
            $page->CurrentRecord = $accessory;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // edit
    #[Route('/AccessoriesEdit/{id:accessory?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'edit.accessories')]
    public function edit(Request $request, AccessoriesEdit $page, ?Entity\Accessory $accessory = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($accessory) {
            $page->CurrentRecord = $accessory;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // delete
    #[Route('/AccessoriesDelete/{id:accessory?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'delete.accessories')]
    public function delete(Request $request, AccessoriesDelete $page, ?Entity\Accessory $accessory = null): Response
    {
        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($accessory) {
            $page->CurrentRecord = $accessory;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }
}
