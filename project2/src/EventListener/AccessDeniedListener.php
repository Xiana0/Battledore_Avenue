<?php

namespace PHPMaker2026\Project1\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use PHPMaker2026\Project1\AdvancedSecurity;

class AccessDeniedListener
{

    public function __construct(protected AdvancedSecurity $security)
    {
    }

    #[AsEventListener]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof AccessDeniedException) {
            return;
        }
        $request = $event->getRequest();

        // Skip AJAX/JSON requests
        if ($request->isXmlHttpRequest() || $request->getPreferredFormat() === 'json') {
            return;
        }
        if ($this->security->getUser() && $request->hasSession()) {
            // Save target path for redirect later
            $this->security->saveLastUrl();

            // Add flash message
            $request->getSession()->getFlashBag()->add('danger', DeniedMessage());
        }
    }
}
