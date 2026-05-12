<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\ResultSetMappingBuilder as BaseResultSetMappingBuilder;
use Doctrine\ORM\Utility\PersisterHelper;
use InvalidArgumentException;

class ResultSetMappingBuilder extends BaseResultSetMappingBuilder
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly int $defaultRenameMode = self::COLUMN_RENAMING_NONE,
    ) {
        parent::__construct($em, $defaultRenameMode);
    }

    /**
     * Adds all fields of the given class to the result set mapping (columns and meta fields).
     *
     * Note: Override to respect options['name'].
     *
     * @param string[] $columnAliasMap
     * @phpstan-param array<string, string> $columnAliasMap
     *
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function addAllClassFields(string $class, string $alias, array $columnAliasMap = []): void
    {
        $classMetadata = $this->em->getClassMetadata($class);
        $platform = $this->em->getConnection()->getDatabasePlatform();
        if (!$this->isInheritanceSupported($classMetadata)) {
            throw new InvalidArgumentException('ResultSetMapping builder does not currently support your inheritance scheme.');
        }

        // Scalar fields
        foreach ($classMetadata->getColumnNames() as $columnName) {
            $propertyName = $classMetadata->getFieldName($columnName);
            $mapping = $classMetadata->getFieldMapping($propertyName);
            $columnAlias = $columnAliasMap[$columnName] ?? $columnName; // Note: Use actual column name, don't use getSQLResultCasing()
            if (isset($this->fieldMappings[$columnAlias])) {
                throw new InvalidArgumentException(sprintf(
                    "The column '%s' conflicts with another column in the mapper.",
                    $columnName,
                ));
            }
            $this->addFieldResult($alias, $columnAlias, $propertyName);
            $enumType = $mapping->enumType ?? null;
            if (! empty($enumType)) {
                $this->addEnumResult($columnAlias, $enumType);
            }
        }

        // Associations / meta fields
        foreach ($classMetadata->associationMappings as $assocMapping) {
            if ($assocMapping->isToOneOwningSide()) {
                $targetClass  = $this->em->getClassMetadata($assocMapping->targetEntity);
                $isIdentifier = $assocMapping['id'] ?? false;
                foreach ($assocMapping->joinColumns as $joinColumn) {
                    $colName = $joinColumn['name'];
                    $columnAlias = $columnAliasMap[$colName] ?? $colName; // Note: Use actual column name, don't use getSQLResultCasing()
                    $columnType = PersisterHelper::getTypeOfColumn($joinColumn['referencedColumnName'], $targetClass, $this->em);
                    if (isset($this->metaMappings[$columnAlias])) {
                        throw new InvalidArgumentException(sprintf(
                            "The column '%s' conflicts with another column in the mapper.",
                            $columnAlias,
                        ));
                    }
                    $this->addMetaResult($alias, $columnAlias, $colName, $isIdentifier, $columnType);
                }
            }
        }
    }

    private function isInheritanceSupported(ClassMetadata $classMetadata): bool
    {
        if (
            $classMetadata->isInheritanceTypeSingleTable()
            && in_array($classMetadata->name, $classMetadata->discriminatorMap, true)
        ) {
            return true;
        }
        return !($classMetadata->isInheritanceTypeSingleTable() || $classMetadata->isInheritanceTypeJoined());
    }
}
