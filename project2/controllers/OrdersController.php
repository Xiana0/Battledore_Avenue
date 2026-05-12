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
 * Orders controller
 */
class OrdersController extends BaseController
{
    // list
    #[Route('/OrdersList', methods: ['GET', 'POST', 'OPTIONS'], name: 'list.orders')]
    public function list(Request $request, OrdersList $page): Response
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
    #[Route('/OrdersAdd/{id:order?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'add.orders')]
    public function add(Request $request, OrdersAdd $page, ?Entity\Order $order = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($order) {
            $page->CurrentRecord = $order;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // view
    #[Route('/OrdersView/{id:order?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'view.orders')]
    public function view(Request $request, OrdersView $page, ?Entity\Order $order = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($order) {
            $page->CurrentRecord = $order;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // edit
    #[Route('/OrdersEdit/{id:order?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'edit.orders')]
    public function edit(Request $request, OrdersEdit $page, ?Entity\Order $order = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($order) {
            $page->CurrentRecord = $order;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // delete
    #[Route('/OrdersDelete/{id:order?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'delete.orders')]
    public function delete(Request $request, OrdersDelete $page, ?Entity\Order $order = null): Response
    {
        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($order) {
            $page->CurrentRecord = $order;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }
}
