<?php

namespace PHPMaker2026\Project1;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Menu Item Adding Event
 */
class MenuItemAddingEvent extends Event
{

    public function __construct(
        protected ?MenuItem $menuItem = null,
        protected ?Menu $menu = null)
    {
    }

    public function getMenuItem(): MenuItem
    {
        return $this->menuItem;
    }

    public function getSubject(): MenuItem
    {
        return $this->menuItem;
    }

    public function setMenuItem(MenuItem $value): void
    {
        $this->menuItem = $value;
    }

    public function getMenu(): Menu
    {
        return $this->menu;
    }

    public function setMenu(Menu $value): void
    {
        $this->menu = $value;
    }
}
