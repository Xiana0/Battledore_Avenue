<?php

namespace PHPMaker2026\Project1;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use InvalidArgumentException;
use RuntimeException;

/**
 * API entity value resolver
 */
class ApiEntityValueResolver implements ValueResolverInterface
{
    protected const ROUTE_MATCHES = ['view', 'edit', 'delete', 'add'];
    protected const ENTITY_CLASS = Entity::class;

    public function __construct(
        protected ManagerRegistry $registry,
        protected AppServiceLocator $locator
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== self::ENTITY_CLASS) {
            return [];
        }
        $routeName = $request->attributes->get('_route', '');
        if (!$routeName) {
            return [];
        }
        $parts = explode('.', $routeName);
        if (count($parts) < 2 || $parts[0] !== 'api') {
            return [];
        }
        $action = $parts[1];
        if (!in_array($action, self::ROUTE_MATCHES, true)) {
            return [];
        }
        $table = $request->attributes->get('table');
        $key = $request->attributes->get('key');
        if (!$table) {
            throw new InvalidArgumentException('Missing {table} parameter in route.');
        }
        $tableObj = $this->locator->get($table);
        if (!isset($tableObj->EntityClass)) {
            throw new RuntimeException("Table '$table' must define an 'EntityClass' property.");
        }
        $entityClass = $tableObj->EntityClass;

        // 'api.add' without key => yield null if nullable
        if ($action === 'add' && $key === null && $argument->isNullable()) {
            yield null;
            return;
        }

        // 'api.delete' without key => multiple delete
        if ($action === 'delete' && $key === null) {
            return [];
        }
        $identifier = $this->parseKey($entityClass, $key);
        $em = $this->registry->getManagerForClass($entityClass);
        if (!$em) {
            throw new RuntimeException("No entity manager found for $entityClass.");
        }

        // Build the page service name like "OrderView" for table=order, action=view
        $pageName = PascalCase($tableObj->TableVar) . PascalCase($action);
        if (!$this->locator->has($pageName)) {
            throw new RuntimeException("Page service '$pageName' not found.");
        }
        $page = $this->locator->get($pageName);
        if (!method_exists($page, 'getListSql')) {
            throw new RuntimeException("Page '$pageName' must have method getListSql().");
        }

        // Get DBAL query builder
        $qb = $page->getListSql()
            ->andWhere($page->arrayToFilter($identifier))
            ->setMaxResults(1);
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata($entityClass, 'e');
        $fieldDefs = method_exists($page, 'getFieldDefinitions') ? $page->getFieldDefinitions() : [];
        $customFields = [];
        $meta = $em->getClassMetadata($entityClass);
        foreach ($fieldDefs as $column => $def) {
            if (($def['IsCustom'] ?? false) && isset($def['Expression'])) {
                $property = GetFieldName($meta, $column);
                $rsm->addScalarResult($column, $property);
                $customFields[] = $column;
            }
        }

        // Create native query and get entity
        $query = $em->createNativeQuery($qb, $rsm);
        $result = $query->getOneOrNullResult();
        if (is_array($result)) {
            $entity = $result[0];
            foreach ($customFields as $field) {
                if (isset($result[$field])) {
                    $entity[$field] = $result[$field];
                }
            }
        } else {
            $entity = $result;
        }
        if (!$entity && !$argument->isNullable()) {
            throw new ResourceNotFoundException("Entity not found for $entityClass with key '$key'.");
        }
        yield $entity;
    }

    protected function parseKey(string $entityClass, ?string $key): ?array
    {
        if ($key === null) {
            return null;
        }
        $em = $this->registry->getManagerForClass($entityClass);
        $meta = $em->getClassMetadata($entityClass);
        $ids = $meta->getIdentifier();
        $keyParts = explode(Config('ROUTE_COMPOSITE_KEY_SEPARATOR'), $key);
        if (count($keyParts) !== count($ids)) {
            throw new InvalidArgumentException("Expected " . count($ids) . " key(s), got " . count($keyParts));
        }
        $ids = array_map(fn($id) => GetColumnName($meta, $id), $ids);
        return array_combine($ids, $keyParts);
    }
}
