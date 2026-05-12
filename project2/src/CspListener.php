<?php

namespace PHPMaker2026\Project1;

use ParagonIE\CSPBuilder\CSPBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * CSP Listener
 */
class CspListener implements EventSubscriberInterface
{
    /**
     * Constructor
     */
    public function __construct(
        protected CSPBuilder $builder
    ) {
    }

    /**
     * Get CSP Builder
     *
     * @return CSPBuilder
     */
    public function getBuilder(): CSPBuilder
    {
        return $this->builder;
    }

    /**
     * Process response
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        if ($request->attributes->get('_disable_csp')) {
            return; // Skip CSP header
        }
        $response = $event->getResponse();
        if ($response->isRedirection()) {
            return;
        }

        // Retrieve the headers
        $cspHeaders = $this->builder->getHeaderArray(false);

        // Set each CSP header on the Response
        foreach ($cspHeaders as $headerName => $headerValue) {
            $response->headers->set($headerName, $headerValue);
        }
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
