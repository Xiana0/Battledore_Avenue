<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PostFlushEvent extends Event
{

    public function __construct(
        private readonly PostFlushEventArgs $doctrineArgs
    ) {}

    public function getDoctrineArgs(): PostFlushEventArgs
    {
        return $this->doctrineArgs;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrineArgs->getObjectManager();
    }
}
