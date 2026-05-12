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
 * Cart controller
 */
class CartController extends BaseController
{
    // list
    #[Route('/CartList', methods: ['GET', 'POST', 'OPTIONS'], name: 'list.cart')]
    public function list(Request $request, CartList $page): Response
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
    #[Route('/CartAdd/{id:cart?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'add.cart')]
    public function add(Request $request, CartAdd $page, ?Entity\Cart $cart = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($cart) {
            $page->CurrentRecord = $cart;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // view
    #[Route('/CartView/{id:cart?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'view.cart')]
    public function view(Request $request, CartView $page, ?Entity\Cart $cart = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($cart) {
            $page->CurrentRecord = $cart;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // edit
    #[Route('/CartEdit/{id:cart?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'edit.cart')]
    public function edit(Request $request, CartEdit $page, ?Entity\Cart $cart = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($cart) {
            $page->CurrentRecord = $cart;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // delete
    #[Route('/CartDelete/{id:cart?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'delete.cart')]
    public function delete(Request $request, CartDelete $page, ?Entity\Cart $cart = null): Response
    {
        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($cart) {
            $page->CurrentRecord = $cart;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }
}
