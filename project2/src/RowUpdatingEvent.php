<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Event\PreUpdateEventArgs as RowUpdatingEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\EventDispatcher\Event;

class RowUpdatingEvent extends Event
{

    public function __construct(
        public readonly RowUpdatingEventArgs $args
    ) {}

    public function getDoctrineArgs(): PreUpdateEventArgs
    {
        return $this->args;
    }

    public function getEntityChangeSet(): array
    {
        return $this->args->getEntityChangeSet();
    }

    public function hasChangedField(string $field): bool
    {
        return $this->args->hasChangedField($field);
    }

    public function getOldValue(string $field): mixed
    {
        return $this->args->getOldValue($field);
    }

    public function getNewValue(string $field): mixed
    {
        return $this->args->getNewValue($field);
    }

    public function setNewValue(string $field, mixed $value): void
    {
        $this->args->setNewValue($field, $value);
    }

    public function getObject(): object
    {
        return $this->args->getObject();
    }

    public function getObjectManager(): object
    {
        return $this->args->getObjectManager();
    }
}
