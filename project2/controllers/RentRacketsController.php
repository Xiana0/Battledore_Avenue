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
 * RentRackets controller
 */
class RentRacketsController extends BaseController
{
    // list
    #[Route('/RentRacketsList', methods: ['GET', 'POST', 'OPTIONS'], name: 'list.rent_rackets')]
    public function list(Request $request, RentRacketsList $page): Response
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
    #[Route('/RentRacketsAdd/{id:rentRacket?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'add.rent_rackets')]
    public function add(Request $request, RentRacketsAdd $page, ?Entity\RentRacket $rentRacket = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($rentRacket) {
            $page->CurrentRecord = $rentRacket;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // view
    #[Route('/RentRacketsView/{id:rentRacket?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'view.rent_rackets')]
    public function view(Request $request, RentRacketsView $page, ?Entity\RentRacket $rentRacket = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($rentRacket) {
            $page->CurrentRecord = $rentRacket;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // edit
    #[Route('/RentRacketsEdit/{id:rentRacket?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'edit.rent_rackets')]
    public function edit(Request $request, RentRacketsEdit $page, ?Entity\RentRacket $rentRacket = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($rentRacket) {
            $page->CurrentRecord = $rentRacket;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // delete
    #[Route('/RentRacketsDelete/{id:rentRacket?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'delete.rent_rackets')]
    public function delete(Request $request, RentRacketsDelete $page, ?Entity\RentRacket $rentRacket = null): Response
    {
        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($rentRacket) {
            $page->CurrentRecord = $rentRacket;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }
}
