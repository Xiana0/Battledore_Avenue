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
 * OrderItems controller
 */
class OrderItemsController extends BaseController
{
    // list
    #[Route('/OrderItemsList', methods: ['GET', 'POST', 'OPTIONS'], name: 'list.order_items')]
    public function list(Request $request, OrderItemsList $page): Response
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
    #[Route('/OrderItemsAdd/{id:orderItem?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'add.order_items')]
    public function add(Request $request, OrderItemsAdd $page, ?Entity\OrderItem $orderItem = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($orderItem) {
            $page->CurrentRecord = $orderItem;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // view
    #[Route('/OrderItemsView/{id:orderItem?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'view.order_items')]
    public function view(Request $request, OrderItemsView $page, ?Entity\OrderItem $orderItem = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($orderItem) {
            $page->CurrentRecord = $orderItem;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // edit
    #[Route('/OrderItemsEdit/{id:orderItem?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'edit.order_items')]
    public function edit(Request $request, OrderItemsEdit $page, ?Entity\OrderItem $orderItem = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($orderItem) {
            $page->CurrentRecord = $orderItem;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // delete
    #[Route('/OrderItemsDelete/{id:orderItem?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'delete.order_items')]
    public function delete(Request $request, OrderItemsDelete $page, ?Entity\OrderItem $orderItem = null): Response
    {
        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($orderItem) {
            $page->CurrentRecord = $orderItem;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }
}
