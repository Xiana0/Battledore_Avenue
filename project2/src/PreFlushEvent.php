<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PreFlushEvent extends Event
{

    public function __construct(
        private readonly PreFlushEventArgs $doctrineArgs
    ) {}

    public function getDoctrineArgs(): PreFlushEventArgs
    {
        return $this->doctrineArgs;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrineArgs->getObjectManager();
    }
}
