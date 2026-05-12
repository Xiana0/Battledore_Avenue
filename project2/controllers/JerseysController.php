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
 * Jerseys controller
 */
class JerseysController extends BaseController
{
    // list
    #[Route('/JerseysList', methods: ['GET', 'POST', 'OPTIONS'], name: 'list.jerseys')]
    public function list(Request $request, JerseysList $page): Response
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
    #[Route('/JerseysAdd/{id:jersey?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'add.jerseys')]
    public function add(Request $request, JerseysAdd $page, ?Entity\Jersey $jersey = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($jersey) {
            $page->CurrentRecord = $jersey;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // view
    #[Route('/JerseysView/{id:jersey?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'view.jerseys')]
    public function view(Request $request, JerseysView $page, ?Entity\Jersey $jersey = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($jersey) {
            $page->CurrentRecord = $jersey;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // edit
    #[Route('/JerseysEdit/{id:jersey?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'edit.jerseys')]
    public function edit(Request $request, JerseysEdit $page, ?Entity\Jersey $jersey = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($jersey) {
            $page->CurrentRecord = $jersey;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // delete
    #[Route('/JerseysDelete/{id:jersey?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'delete.jerseys')]
    public function delete(Request $request, JerseysDelete $page, ?Entity\Jersey $jersey = null): Response
    {
        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($jersey) {
            $page->CurrentRecord = $jersey;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }
}
