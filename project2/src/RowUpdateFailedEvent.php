<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\EventDispatcher\GenericEvent;
use Throwable;

class RowUpdateFailedEvent extends GenericEvent
{

    public function __construct(
        protected readonly Entity $entity,
        protected readonly Throwable $exception,
    ) {
        parent::__construct($entity, [
            'exception' => $exception,
            'action' => 'U',
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
