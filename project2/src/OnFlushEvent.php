<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Symfony\Contracts\EventDispatcher\Event;

class OnFlushEvent extends Event
{

    public function __construct(
        private readonly OnFlushEventArgs $doctrineArgs
    ) {}

    public function getDoctrineArgs(): OnFlushEventArgs
    {
        return $this->doctrineArgs;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrineArgs->getObjectManager();
    }

    public function getUnitOfWork(): UnitOfWork
    {
        return $this->getEntityManager()->getUnitOfWork();
    }

    public function getScheduledChanges(): array
    {
        $uow = $this->getUnitOfWork();
        return [
            'insertions' => $uow->getScheduledEntityInsertions(),
            'updates' => $uow->getScheduledEntityUpdates(),
            'deletions' => $uow->getScheduledEntityDeletions(),
        ];
    }
}
