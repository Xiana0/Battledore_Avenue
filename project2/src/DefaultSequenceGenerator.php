<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;

/**
 * Dummy generator for columns with sequence in Oracle/PostgreSQL
 *
 * This generator avoids prefetching the sequence to prevent double increments.
 * It returns temporary unique negative IDs to satisfy Doctrine's identity map
 * when inserting multiple new entities in the same UnitOfWork.
 */
class DefaultSequenceGenerator extends AbstractIdGenerator
{
    /**
     * Counter for temporary in-memory IDs
     *
     * Negative values ensure no collision with real database-generated IDs.
     *
     * @var int
     */
    protected static int $counter = -1;

    /**
     * Generates a temporary ID for a new entity
     *
     * Doctrine requires each entity to have a unique identifier in the identity map.
     * This method returns a unique negative integer for each new entity.
     * After flush, the database-generated sequence/identity value will replace this temporary ID.
     *
     * @param EntityManagerInterface $em The entity manager handling the entity
     * @param object|null $entity The entity for which to generate an ID
     *
     * @return int Temporary unique ID for Doctrine's identity map
     */
    public function generateId(EntityManagerInterface $em, object|null $entity): int
    {
        // Generate a unique negative ID in memory
        return self::$counter--;
    }
}
