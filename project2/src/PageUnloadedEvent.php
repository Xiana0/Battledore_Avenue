<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Page Unloaded Event
 */
class PageUnloadedEvent extends GenericEvent
{

    public function getPage(): mixed
    {
        return $this->subject;
    }
}
