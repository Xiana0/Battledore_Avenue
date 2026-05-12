<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Event\PostPersistEventArgs as RowInsertedEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\EventDispatcher\Event;

class RowInsertedEvent extends Event
{

    public function __construct(
        public readonly RowInsertedEventArgs $args
    ) {}

    public function getDoctrineArgs(): PostPersistEventArgs
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
