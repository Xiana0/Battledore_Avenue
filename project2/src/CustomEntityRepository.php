<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Query;

/**
 * Generic Doctrine repository that automatically skips fields
 * marked with options["custom"] = true in the mapping.
 *
 * Works with Doctrine ORM 3 and DBAL 4.
 * Does not require Symfony service registration.
 *
 * @template T of object
 * @extends EntityRepository<T>
 */
class CustomEntityRepository extends EntityRepository
{
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em    The Doctrine entity manager.
     * @param ClassMetadata<T>        $class The class metadata for the entity.
     */
    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        parent::__construct($em, $class);
    }

    /**
     * Get all field names that have options["custom"] = true.
     *
     * @return string[] List of custom field names.
     */
    protected function getCustomFields(): array
    {
        $meta = $this->getClassMetadata();
        $customFields = [];
        foreach ($meta->getFieldNames() as $fieldName) {
            $mapping = $meta->getFieldMapping($fieldName);
            if (($mapping->options['custom'] ?? false) === true) {
                $customFields[] = $fieldName;
            }
        }
        return $customFields;
    }

    /**
     * Create a partial QueryBuilder selecting only non-custom fields.
     *
     * @param string $alias SQL alias for the entity table.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createPartialQueryBuilder(string $alias = 'e')
    {
        $meta = $this->getClassMetadata();
        $allFields = $meta->getFieldNames();
        $fieldsToSelect = array_diff($allFields, $this->getCustomFields());
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('partial ' . $alias . '.{' . implode(',', $fieldsToSelect) . '}')
            ->from($meta->getName(), $alias);
    }

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param mixed                     $id          The entity identifier.
     * @param LockMode|int|null         $lockMode    One of the LockMode constants or null.
     * @param int|null                  $lockVersion The lock version for optimistic locking.
     *
     * @return object|null The entity instance or null if not found.
     */
    public function find(
        mixed $id,
        LockMode|int|null $lockMode = null,
        int|null $lockVersion = null
    ): object|null {
        $customFields = $this->getCustomFields();
        if (empty($customFields)) {
            return parent::find($id, $lockMode, $lockVersion);
        }
        $meta = $this->getClassMetadata();
        $identifier = $meta->getIdentifier()[0] ?? 'id';
        $result = $this->findBy([$identifier => $id], null, 1);
        return $result[0] ?? null;
    }

    /**
     * Finds all entities in the repository.
     *
     * @phpstan-return list<T> The entities.
     */
    public function findAll(): array
    {
        if (empty($this->getCustomFields())) {
            return parent::findAll();
        }
        return $this->createPartialQueryBuilder('e')
            ->getQuery()
            ->getResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Finds entities by a set of criteria.
     *
     * @param array<string,mixed>        $criteria Criteria (field => value) pairs.
     * @param array<string,string>|null  $orderBy  Optional order by array.
     * @param int|null                   $limit    Maximum results.
     * @param int|null                   $offset   Offset for pagination.
     *
     * @phpstan-return list<T>
     */
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null
    ): array {
        if (empty($this->getCustomFields())) {
            return parent::findBy($criteria, $orderBy, $limit, $offset);
        }
        $qb = $this->createPartialQueryBuilder('e');
        foreach ($criteria as $field => $value) {
            $qb->andWhere("e.$field = :$field")->setParameter($field, $value);
        }
        if ($orderBy) {
            foreach ($orderBy as $field => $dir) {
                $qb->addOrderBy("e.$field", $dir);
            }
        }
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }
        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Finds a single entity by a set of criteria.
     *
     * @param array<string,mixed>       $criteria Criteria (field => value) pairs.
     * @param array<string,string>|null $orderBy  Optional order by array.
     *
     * @return object|null The entity instance or null if not found.
     *
     * @phpstan-return T|null
     */
    public function findOneBy(
        array $criteria,
        array|null $orderBy = null
    ): object|null {
        if (empty($this->getCustomFields())) {
            return parent::findOneBy($criteria, $orderBy);
        }
        $result = $this->findBy($criteria, $orderBy, 1);
        return $result[0] ?? null;
    }
}
