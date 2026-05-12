<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Event\PostUpdateEventArgs as RowUpdatedEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\EventDispatcher\Event;

class RowUpdatedEvent extends Event
{

    public function __construct(
        public readonly RowUpdatedEventArgs $args,
        private readonly array $entityChangeSet
    ) {}

    public function getDoctrineArgs(): PostUpdateEventArgs
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

    public function getEntityChangeSet(): array
    {
        return $this->entityChangeSet;
    }
}
