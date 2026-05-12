<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Event\PostRemoveEventArgs as RowDeletedEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\EventDispatcher\Event;

class RowDeletedEvent extends Event
{

    public function __construct(
        public readonly RowDeletedEventArgs $args
    ) {}

    public function getDoctrineArgs(): PostRemoveEventArgs
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
