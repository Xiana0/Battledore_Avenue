<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Menu Rendering Event
 */
class MenuRenderingEvent extends GenericEvent
{

    public function getMenu(): Menu
    {
        return $this->subject;
    }
}
