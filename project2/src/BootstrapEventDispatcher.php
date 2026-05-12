<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BootstrapEventDispatcher extends EventDispatcher
{
    private bool $locked = false;
    private ?EventDispatcherInterface $mainDispatcher = null;

    public function addListener(string $eventName, callable|array $listener, int $priority = 0): void
    {
        if ($this->locked && $this->mainDispatcher) {
            $this->mainDispatcher->addListener($eventName, $listener, $priority);
        } else {
            parent::addListener($eventName, $listener, $priority);
        }
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        if ($this->locked && $this->mainDispatcher) {
            return $this->mainDispatcher->dispatch($event, $eventName);
        }
        return parent::dispatch($event, $eventName);
    }

    public function lockAndFlushTo(EventDispatcherInterface $mainDispatcher): void
    {
        if ($this->locked) {
            return;
        }
        $this->mainDispatcher = $mainDispatcher;
        $this->locked = true;

        // Copy listeners to the main dispatcher
        foreach ($this->getListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $mainDispatcher->addListener($eventName, $listener);
            }
        }

        // Clear listeners from the current dispatcher
        foreach ($this->getListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $this->removeListener($eventName, $listener);
            }
        }
    }
}
