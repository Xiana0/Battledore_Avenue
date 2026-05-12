<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PostLoadEvent extends Event
{

    public function __construct(
        private readonly PostLoadEventArgs $doctrineArgs
    ) {}

    public function getDoctrineArgs(): PostLoadEventArgs
    {
        return $this->doctrineArgs;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrineArgs->getObjectManager();
    }

    public function getEntity(): object
    {
        return $this->doctrineArgs->getObject();
    }
}
