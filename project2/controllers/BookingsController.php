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
 * Bookings controller
 */
class BookingsController extends BaseController
{
    // list
    #[Route('/BookingsList', methods: ['GET', 'POST', 'OPTIONS'], name: 'list.bookings')]
    public function list(Request $request, BookingsList $page): Response
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
    #[Route('/BookingsAdd/{id:booking?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'add.bookings')]
    public function add(Request $request, BookingsAdd $page, ?Entity\Booking $booking = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($booking) {
            $page->CurrentRecord = $booking;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // view
    #[Route('/BookingsView/{id:booking?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'view.bookings')]
    public function view(Request $request, BookingsView $page, ?Entity\Booking $booking = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($booking) {
            $page->CurrentRecord = $booking;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // edit
    #[Route('/BookingsEdit/{id:booking?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'edit.bookings')]
    public function edit(Request $request, BookingsEdit $page, ?Entity\Booking $booking = null): Response
    {
        // Init page
        $page->init();

        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($booking) {
            $page->CurrentRecord = $booking;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }

    // delete
    #[Route('/BookingsDelete/{id:booking?}', methods: ['GET', 'POST', 'OPTIONS'], name: 'delete.bookings')]
    public function delete(Request $request, BookingsDelete $page, ?Entity\Booking $booking = null): Response
    {
        // Check resolved arguments
        $hasResolved = false;

        // Set current record
        if ($booking) {
            $page->CurrentRecord = $booking;
            $hasResolved = true;
        }

        // Run page
        return $this->runPage($page);
    }
}
