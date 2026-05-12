<?php

namespace PHPMaker2026\Project1;

use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Contracts\EventDispatcher\Event;

class LoadClassMetadataEvent extends Event
{

    public function __construct(
        private readonly LoadClassMetadataEventArgs $doctrineArgs
    ) {}

    public function getDoctrineArgs(): LoadClassMetadataEventArgs
    {
        return $this->doctrineArgs;
    }

    public function getClassMetadata(): ClassMetadata
    {
        return $this->doctrineArgs->getClassMetadata();
    }
}
