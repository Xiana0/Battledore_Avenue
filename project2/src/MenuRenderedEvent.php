<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Menu Rendered Event
 */
class MenuRenderedEvent extends GenericEvent
{

    public function getMenu(): Menu
    {
        return $this->subject;
    }
}
