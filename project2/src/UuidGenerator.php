<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Ramsey\Uuid\Uuid;

class UuidGenerator extends AbstractIdGenerator
{
    /**
     * @param object $entity
     */
    public function generateId(EntityManagerInterface $em, object|null $entity): mixed
    {
        return Uuid::uuid4()->toString();
    }
}
