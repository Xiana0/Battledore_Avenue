<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Event\PreRemoveEventArgs as RowDeletingEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\EventDispatcher\Event;

class RowDeletingEvent extends Event
{

    public function __construct(
        public readonly RowDeletingEventArgs $args
    ) {}

    public function getDoctrineArgs(): PreRemoveEventArgs
    {
        return $this->args;
    }

    public function getObject(): object
    {
        return $this->args->getObject();
    }

    public function getObjectManager(): ObjectManager
    {
        return $this->args->getObjectManager();
    }
}
