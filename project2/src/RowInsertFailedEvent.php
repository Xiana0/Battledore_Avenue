<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\EventDispatcher\GenericEvent;
use Throwable;

class RowInsertFailedEvent extends GenericEvent
{

    public function __construct(
        public readonly Entity $entity,
        public readonly Throwable $exception
    ) {
        parent::__construct($entity, [
            'exception' => $exception,
            'action' => 'A',
        ]);
    }

    public function getAction(): string
    {
        return $this->getArgument('action');
    }

    public function getException(): Throwable
    {
        return $this->getArgument('exception');
    }
}
