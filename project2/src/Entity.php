<?php

namespace PHPMaker2026\Project1;

use Doctrine\ORM\Mapping\ClassMetadata;
use ReflectionClass;
use ReflectionProperty;
use IteratorAggregate;
use ArrayIterator;
use Traversable;
use ArrayAccess;
use stdClass;
use DateTime;
use DateTimeImmutable;
use RuntimeException;
use InvalidArgumentException;
use Throwable;

/**
 * Abstract entity class
 */
abstract class Entity implements ArrayAccess, IteratorAggregate
{
    protected ?ClassMetadata $classMetadata = null;
    protected ?array $fieldNames = null;

    /**
     * Returns an array of column names
     *
     * @return array<string, mixed> Field names keyed by column names
     */
    public function getFieldNames(): array
    {
        if (!$this->fieldNames) {
            $metaData = $this->metaData();
        }
        return $this->fieldNames;
    }

    /**
     * Get Doctrine metadata for the current entity
     *
     * @return ClassMetadata<object>
     */
    public function metaData(): ClassMetadata
    {
        if (!isset($this->classMetadata)) {
            $className = get_class($this);
            $em = Container('doctrine')->getManagerForClass($className);
            if (!$em) {
                throw new RuntimeException("No EntityManager found for class $className");
            }

            /** @var ClassMetadata<object> $metadata */
            $metadata = $em->getClassMetadata($className);
            $this->classMetadata = $metadata;
            $fieldNames = [];
            foreach ($metadata->fieldMappings as $name => $mapping) {
                $columnName = $mapping->options['name'] ?? $mapping->columnName ?? $name;
                $fieldNames[$columnName] = $name;
            }
            $this->fieldNames = $fieldNames;
        }
        return $this->classMetadata;
    }

    /**
     * Check if property is initialized
     *
     * @param string $property Property name (camelCase)
     * @return bool
     */
    public function isInitialized(string $property): bool
    {
        $reflClass = $this->metaData()->reflClass;
        if (!$reflClass->hasProperty($property)) {
            return false;
        }
        $reflField = $reflClass->getProperty($property);
        return $reflField->isInitialized($this);
    }

    /**
     * Get primary key value
     * Note: Return the first primary key only, does not support composite key.
     *
     * @return mixed
     */
    public function id(): mixed
    {
        return array_values($this->metaData()->getIdentifierValues($this))[0] ?? null;
    }

    /**
     * Get primary key value(s)
     * Note: Return the primary key as array (support composite key)
     *
     * @return array
     */
    public function identifierValues(): array
    {
        return $this->metaData()->getIdentifierValues($this);
    }

    /**
     * Get identifier values as string
     *
     * @return string
     */
    public function identifierValuesAsString(): string
    {
        return implode(Config('COMPOSITE_KEY_SEPARATOR'), array_map(
            fn($v) => $v instanceof DateTime || $v instanceof DateTimeImmutable ? $v->format('Y-m-d H:i:s') : $v,
            $this->identifierValues()
        ));
    }

    /**
     * Get the property name corresponding to a database column
     *
     * @param string $columnName The database column name.
     * @return string The entity property name, or the column name itself if no mapping exists.
     */
    public function fieldName(string $columnName): string
    {
        $this->fieldNames ??= $this->getFieldNames();
        return $this->fieldNames[$columnName] ?? $columnName;
    }

    /**
     * Get the database column name corresponding to an entity property
     *
     * @param string $propertyName The entity property name.
     * @return string The database column name, or the property name itself if no mapping exists.
     */
    public function columnName(string $propertyName): string
    {
        $this->fieldNames ??= $this->getFieldNames();

        // Reverse lookup
        $column = array_search($propertyName, $this->fieldNames, true);
        return $column ?: $propertyName;
    }

    /**
     * Has column name
     *
     * @param string $name Column name
     * @return bool
     */
    public function has(string $columnName): bool
    {
        return array_key_exists($columnName, $this->getFieldNames());
    }

    /**
     * Get value by column name
     *
     * @param string $name Column name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        $fieldName = $this->fieldName($name);
        $method = 'get' . PascalCase($fieldName);
        if (!$this->isInitialized($fieldName)) {
            return null;
        }
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->$fieldName;
    }

    /**
     * Set value by column name
     *
     * @param string $name Column name
     * @param mixed $value Value
     * @return static
     */
    public function set(string $name, mixed $value): static
    {
        $fieldName = $this->fieldName($name);
        $method = 'set' . PascalCase($fieldName);
        if (!property_exists($this, $fieldName)) {
            return $this; // Property doesn't exist
        }
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->$fieldName = $value;
        }
        return $this;
    }

    /**
     * Convert to array with column or param name as keys
     *
     * @param bool $useParamName Use param name or column name
     * @return array
     */
    public function toArray(bool $useParamName = false): array
    {
        $meta = $this->metaData();
        $fieldMappings = $meta->fieldMappings;
        return array_reduce(
            $meta->getFieldNames(),
            function (array $result, string $property) use ($useParamName, $fieldMappings) {
                $mapping = $fieldMappings[$property];
                $key = $useParamName
                    ? $mapping->options['param'] ?? $mapping->options['name'] ?? $mapping->columnName
                    : $mapping->options['name'] ?? $mapping->columnName;
                $result[$key] = $this->isInitialized($property) ? $this->get($property) : null;
                return $result;
            },
            []
        );
    }

    /**
     * Populate entity from an associative array (e.g. DBAL record)
     *
     * @param array<string, mixed> $data Key-value pairs, where keys are column names.
     * @return static
     */
    public function fromArray(array $data): static
    {
        $metadata = $this->metaData();
        $fieldMap = $this->fieldNames ?? [];
        foreach ($data as $column => $value) {
            $field = $fieldMap[$column] ?? $column;
            if (!$metadata->hasField($field)) {
                $this->set($field, $value); // Allow setting unknown/virtual fields
                continue;
            }
            $type = $metadata->getTypeOfField($field);
            if ($value !== null) {
                switch ($type) {
                    case 'datetime':
                    case 'datetimetz':
                    case 'date':
                    case 'time':
                        if ($value instanceof DateTimeImmutable) {
                            $value = DateTime::createFromImmutable($value);
                        } elseif (is_string($value)) {
                            $parsed = ParseDateTime($value);
                            if ($parsed !== false) {
                                $value = $parsed instanceof DateTimeImmutable ? DateTime::createFromImmutable($parsed) : $parsed;
                            } elseif ($metadata->isNullable($field)) {
                                $value = null;
                            }
                        }
                        break;
                    case 'boolean':
                        $value = $metadata->isNullable($field) && IsEmpty($value)
                            ? null
                            : ConvertToBool($value);
                        break;
                    case 'integer':
                    case 'bigint':
                    case 'smallint':
                        if ($metadata->isNullable($field) && !is_numeric($value)) {
                            $value = null;
                        } else {
                            $value = (int) $value;
                        }
                        break;
                    case 'float':
                    case 'decimal':
                        if ($metadata->isNullable($field) && !is_numeric($value)) {
                            $value = null;
                        } else {
                            $value = (float) $value;
                        }
                        break;
                    case 'json':
                    case 'array':
                    case 'simple_array':
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $value = $decoded;
                            }
                        }
                        break;
                }
            }
            $this->set($field, $value);
        }
        return $this;
    }

    /**
     * Create an entity instance from an array
     *
     * @param array $data
     * @return static
     */
    public static function createFromArray(array $data): static
    {
        $class = new ReflectionClass(static::class);

        /** @var static $instance */
        $instance = $class->newInstanceWithoutConstructor();
        return $instance->fromArray($data);
    }

    /**
     * Get iterator
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->getFieldNames());
    }

    /**
     * Offset get
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Offset set
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Offset unset
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $fieldName = $this->fieldName($offset);
        if (property_exists($this, $fieldName)) {
            unset($this->$fieldName);
        }
    }

    /**
     * Serialize only Doctrine-mapped field properties.
     *
     * @return array<string, mixed> Serialized property values
     */
    public function __serialize(): array
    {
        $meta = $this->metaData();
        $data = [];
        foreach ($meta->getFieldNames() as $property) {
            $mapping = $meta->getFieldMapping($property);

            // Skip fields explicitly marked as binary/blob in options
            if (($mapping->options['blob'] ?? false) === true) {
                continue;
            }
            if ($this->isInitialized($property)) {
                $value = $this->get($property);

                // Skip unserializable resources
                if (is_resource($value)) {
                    continue;
                }
                $data[$property] = $value;
            }
        }
        return $data;
    }

    /**
     * Restore entity state from serialized data.
     *
     * @param array<string, mixed> $data Serialized property values
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $fieldNames = $this->metaData()->getFieldNames();
        foreach ($data as $property => $value) {
            if (in_array($property, $fieldNames, true)) {
                $this->set($property, $value); // Uses getter/setter if available
            }
        }
    }

    /**
     * Magic getter to support snake_case (or other case) access
     *
     * @param string $name Property name in snake_case
     * @return mixed|null
     */
    public function __get(string $name): mixed
    {
        $property = CamelCase($name);
        $getter = 'get' . PascalCase($property);
        if (!$this->isInitialized($property)) {
            return null;
        }
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        return $this->$property;
    }

    /**
     * Magic setter to support snake_case (or other case) assignment
     *
     * @param string $name Property name in snake_case
     * @param mixed $value Value to assign
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $property = CamelCase($name);
        if (!in_array($property, $this->metaData()->getFieldNames(), true)) {
            return;
        }
        $setter = 'set' . PascalCase($property);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $this->$property = $value;
        }
    }

    /**
     * Convert entity to stdClass object
     *
     * @param callable|null $keyConverter Optional function to convert property names
     *                                    Signature: fn(string $propertyName): string
     * @return stdClass
     */
    public function toObject(?callable $keyConverter = null): stdClass
    {
        $data = $this->__serialize();
        if ($keyConverter !== null) {
            $converted = [];
            foreach ($data as $key => $value) {
                $converted[$keyConverter($key)] = $value;
            }
            $data = $converted;
        }
        return (object) $data;
    }
}
