<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Page Rendering Event
 */
class PageRenderingEvent extends GenericEvent
{

    public function getPage(): mixed
    {
        return $this->subject;
    }
}
