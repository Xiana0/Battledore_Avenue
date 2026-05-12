<?php

namespace PHPMaker2026\Project1\EventListener;

use PHPMaker2026\Project1\AppServiceLocator;
use PHPMaker2026\Project1\Language;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use LogicException;
use function PHPMaker2026\Project1\PascalCase;
use function PHPMaker2026\Project1\GetColumnName;
use function PHPMaker2026\Project1\GetClassShortName;

/**
 * Cascade Update/Delete Listener
 */
#[AsDoctrineListener(event: Events::onFlush)]
class CascadeUpdateDeleteListener
{

    public function __construct(
        protected ManagerRegistry $registry,
        protected AppServiceLocator $locator,
        protected Language $language,
        #[Autowire('%app.relations_config%')]
        protected array $relationsConfig,
    ) {}

    public function onFlush(OnFlushEventArgs $args): void
    {
        /** @var EntityManagerInterface $em */
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        // Handle updates
        foreach ($uow->getScheduledEntityUpdates() as $master) {
            $masterClass = get_class($master);
            foreach ($this->relationsConfig as $relation) {
                if ($relation['MasterEntity'] !== $masterClass) {
                    continue;
                }
                $detailClass = $relation['DetailEntity'];
                $fieldMapping = $this->mapFields($relation['Relations']);
                $cascadeUpdate = $relation['CascadeUpdate'] ?? false;
                $enforceRI = $relation['EnforceReferentialIntegrity'] ?? false;
                if ($cascadeUpdate) {
                    $changeSet = $uow->getEntityChangeSet($master);
                    $this->doCascadeUpdateOnFlush($master, $detailClass, $fieldMapping, $em, $uow, $changeSet);
                } elseif ($enforceRI) {
                    $this->checkReferentialIntegrityBeforeUpdate($master, $detailClass, $fieldMapping, $em, $uow);
                }
            }
        }

        // Handle deletes
        foreach ($uow->getScheduledEntityDeletions() as $master) {
            $masterClass = get_class($master);
            foreach ($this->relationsConfig as $relation) {
                if ($relation['MasterEntity'] !== $masterClass) {
                    continue;
                }
                $detailClass = $relation['DetailEntity'];
                $fieldMapping = $this->mapFields($relation['Relations']);
                $cascadeDelete = $relation['CascadeDelete'] ?? false;
                $enforceRI = $relation['EnforceReferentialIntegrity'] ?? false;
                if ($cascadeDelete) {
                    $this->doCascadeDeleteOnFlush($master, $detailClass, $fieldMapping, $em, $uow);
                } elseif ($enforceRI) {
                    $this->checkReferentialIntegrityBeforeDelete($master, $detailClass, $fieldMapping, $em);
                }
            }
        }
    }
    protected function doCascadeUpdateOnFlush(
        object $master,
        string $detailClass,
        array $fieldMapping,
        EntityManagerInterface $em,
        UnitOfWork $uow,
        array $changeSet
    ): void {
        if (empty($changeSet)) {
            return; // nothing changed, skip
        }
        $meta = $em->getClassMetadata($detailClass);
        $tableName = $meta->getTableName();
        $detailTable = $this->locator->get($tableName);

        // Build criteria to find detail records: old values for changed fields, current for unchanged
        $criteria = [];
        foreach ($fieldMapping as $detailField => $masterField) {
            if (isset($changeSet[$masterField])) {
                $criteria[GetColumnName($meta, $detailField)] = $changeSet[$masterField][0];
            } else {
                $getter = 'get' . PascalCase($masterField);
                $criteria[GetColumnName($meta, $detailField)] = $master->$getter();
            }
        }

        // Make sure criteria is not empty
        if (empty($detailTable->arrayToFilter($criteria))) {
            return; // Nothing to update, skip
        }
        $details = $detailTable->loadEntitiesFromFilter($criteria);
        foreach ($details as $detail) {
            // Check if entity is managed, if not, fetch it from database
            if (!$em->contains($detail)) {
                $identifierValues = $meta->getIdentifierValues($detail);
                $managedDetail = $em->find($detailClass, $identifierValues);
                if (!$managedDetail) { // Entity doesn't exist in DB, skip or handle error
                    continue;
                }
                $detail = $managedDetail;
            }
            foreach ($fieldMapping as $detailField => $masterField) {
                if (isset($changeSet[$masterField])) {
                    $setter = 'set' . PascalCase($detailField);
                    $getter = 'get' . PascalCase($masterField);
                    if (method_exists($detail, $setter) && method_exists($master, $getter)) {
                        $detail->$setter($master->$getter());
                    }
                }
            }
            $uow->recomputeSingleEntityChangeSet($meta, $detail);
        }
    }
    protected function doCascadeDeleteOnFlush(
        object $master,
        string $detailClass,
        array $fieldMapping,
        EntityManagerInterface $em,
        UnitOfWork $uow
    ): void {
        $meta = $em->getClassMetadata($detailClass);
        $tableName = $meta->getTableName();
        $detailTable = $this->locator->get($tableName);

        // Build criteria based on current master values
        $criteria = [];
        foreach ($fieldMapping as $detailField => $masterField) {
            $getter = 'get' . PascalCase($masterField);
            $criteria[GetColumnName($meta, $detailField)] = $master->$getter();
        }

        // Make sure criteria is not empty
        if (empty($detailTable->arrayToFilter($criteria))) {
            return; // Nothing to delete, skip
        }
        $details = $detailTable->loadEntitiesFromFilter($criteria);
        foreach ($details as $detail) {
            if (!$em->contains($detail)) {
                $identifierValues = $meta->getIdentifierValues($detail);
                $managedDetail = $em->find($detailClass, $identifierValues);
                if (!$managedDetail) { // Entity doesn't exist in DB, skip
                    continue;
                }
                $detail = $managedDetail;
            }
            $uow->scheduleForDelete($detail);
        }
    }
    protected function checkReferentialIntegrityBeforeUpdate(
        object $master,
        string $detailClass,
        array $fieldMapping,
        EntityManagerInterface $em,
        UnitOfWork $uow
    ): void {
        $changeSet = $uow->getEntityChangeSet($master);
        $hasChanged = false;
        foreach ($fieldMapping as $masterField) {
            if (isset($changeSet[$masterField])) {
                $hasChanged = true;
                break;
            }
        }
        if (!$hasChanged) {
            return;
        }
        $criteria = [];
        foreach ($fieldMapping as $detailField => $masterField) {
            $criteria[$detailField] = $changeSet[$masterField][0];
        }
        $count = $em->getRepository($detailClass)->count($criteria);
        if ($count > 0) {
            throw new LogicException(sprintf(
                $this->language->phrase('RelatedRecordExistsUpdate'),
                GetClassShortName($master),
                $count,
                GetClassShortName($detailClass)
            ));
        }
    }
    protected function checkReferentialIntegrityBeforeDelete(
        object $master,
        string $detailClass,
        array $fieldMapping,
        EntityManagerInterface $em
    ): void {
        $criteria = [];
        foreach ($fieldMapping as $detailField => $masterField) {
            $getter = 'get' . PascalCase($masterField);
            $criteria[$detailField] = $master->$getter();
        }
        $count = $em->getRepository($detailClass)->count($criteria);
        if ($count > 0) {
            throw new LogicException(sprintf(
                $this->language->phrase('RelatedRecordExistsDelete'),
                GetClassShortName($master),
                $count,
                GetClassShortName($detailClass)
            ));
        }
    }
    protected function mapFields(array $relations): array
    {
        $map = [];
        foreach ($relations as $rel) {
            $map[$rel['DetailField']] = $rel['MasterField'];
        }
        return $map;
    }
}
