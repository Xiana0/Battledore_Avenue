<?php

namespace PHPMaker2026\Project1\EventListener;

use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs as RowUpdatingEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs as RowUpdatedEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs as RowInsertingEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs as RowInsertedEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs as RowDeletingEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs as RowDeletedEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use PHPMaker2026\Project1\RowUpdatingEvent;
use PHPMaker2026\Project1\RowUpdatedEvent;
use PHPMaker2026\Project1\RowInsertingEvent;
use PHPMaker2026\Project1\RowInsertedEvent;
use PHPMaker2026\Project1\RowDeletingEvent;
use PHPMaker2026\Project1\RowDeletedEvent;
use PHPMaker2026\Project1\PreFlushEvent;
use PHPMaker2026\Project1\OnFlushEvent;
use PHPMaker2026\Project1\PostFlushEvent;
use PHPMaker2026\Project1\PostLoadEvent;
use PHPMaker2026\Project1\LoadClassMetadataEvent;
use function PHPMaker2026\Project1\GetColumnName;

#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::postRemove)]
#[AsDoctrineListener(event: Events::preFlush)]
#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
#[AsDoctrineListener(event: Events::postLoad)]
#[AsDoctrineListener(event: Events::loadClassMetadata)]
class DoctrineEventListener
{
    /**
     * Stores change sets for postUpdate processing.
     *
     * @var array<int, array<string, array{mixed, mixed}>>
     */
    private array $changeSets = [];

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    /* -----------------------------------------------------------------
     * UPDATE EVENTS
     * -----------------------------------------------------------------*/
    public function preUpdate(RowUpdatingEventArgs $args): void
    {
        $entity = $args->getObject();
        $oid = spl_object_id($entity);

        // Store change set for later use in postUpdate
        $this->changeSets[$oid] = $args->getEntityChangeSet();

        // Dispatch custom preUpdate event
        $this->dispatcher->dispatch(new RowUpdatingEvent($args));
    }
    public function postUpdate(RowUpdatedEventArgs $args): void
    {
        $entity = $args->getObject();
        $oid = spl_object_id($entity);
        $changeSet = $this->changeSets[$oid] ?? [];
        unset($this->changeSets[$oid]); // Clean up early
        if ($changeSet !== []) {
            $metadata = $entity->metaData();
            $mappedChangeSet = [];
            foreach ($changeSet as $propertyName => $values) {
                // Use helper function to resolve DB column name
                $fieldName = GetColumnName($metadata, $propertyName);
                $mappedChangeSet[$fieldName] = $values;
            }
            $changeSet = $mappedChangeSet;
        }

        // Dispatch custom postUpdate event with mapped change set
        $this->dispatcher->dispatch(new RowUpdatedEvent($args, $changeSet));
    }

    /* -----------------------------------------------------------------
     * INSERT EVENTS
     * -----------------------------------------------------------------*/
    public function prePersist(RowInsertingEventArgs $args): void
    {
        $this->dispatcher->dispatch(new RowInsertingEvent($args));
    }
    public function postPersist(RowInsertedEventArgs $args): void
    {
        $this->dispatcher->dispatch(new RowInsertedEvent($args));
    }

    /* -----------------------------------------------------------------
     * DELETE EVENTS
     * -----------------------------------------------------------------*/
    public function preRemove(RowDeletingEventArgs $args): void
    {
        $this->dispatcher->dispatch(new RowDeletingEvent($args));
    }
    public function postRemove(RowDeletedEventArgs $args): void
    {
        $this->dispatcher->dispatch(new RowDeletedEvent($args));
    }

    /* -----------------------------------------------------------------
     * FLUSH EVENTS
     * -----------------------------------------------------------------*/
    public function preFlush(PreFlushEventArgs $args): void
    {
        $this->dispatcher->dispatch(new PreFlushEvent($args));
    }
    public function onFlush(OnFlushEventArgs $args): void
    {
        $this->dispatcher->dispatch(new OnFlushEvent($args));
    }
    public function postFlush(PostFlushEventArgs $args): void
    {
        $this->changeSets = []; // cleanup
        $this->dispatcher->dispatch(new PostFlushEvent($args));
    }

    /* -----------------------------------------------------------------
     * METADATA AND LOAD EVENTS
     * -----------------------------------------------------------------*/
    public function postLoad(PostLoadEventArgs $args): void
    {
        $this->dispatcher->dispatch(new PostLoadEvent($args));
    }
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $this->dispatcher->dispatch(new LoadClassMetadataEvent($args));
    }
}
