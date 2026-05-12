<?php

namespace PHPMaker2026\Project1;

use Psr\Container\ContainerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Custom entity value resolver
 */
class CustomEntityValueResolver implements ValueResolverInterface
{

    public function __construct(
        protected ManagerRegistry $registry,
        protected ContainerInterface $container,
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $entityClass = $argument->getType();

        // Skip if argument has no type or class does not exist
        if (!$entityClass || !class_exists($entityClass)) {
            return [];
        }

        // Skip if argument is not a Doctrine-managed entity
        $em = $this->registry->getManagerForClass($entityClass);
        if (!$em) {
            return [];
        }
        $meta = $em->getClassMetadata($entityClass);
        $idFields = $meta->getIdentifier();

        // Get route parameters for entity IDs
        $params = $request->attributes->get('_route_params', []);
        $criteria = [];
        foreach ($idFields as $field) {
            if (!isset($params[$field]) || $params[$field] === null) {
                // Optional param missing, inject null and stop chain
                yield null;
                return;
            }
            $criteria[GetColumnName($meta, $field)] = $params[$field];
        }

        // Resolve page service
        $routeName = $request->attributes->get('_route', '');
        if (!$routeName) {
            yield null;
            return;
        }
        $pageName = RouteNameToPageName($routeName);
        if (!$this->container->has($pageName)) {
            yield null;
            return;
        }
        $page = $this->container->get($pageName);
        if (!method_exists($page, 'getListSql')) {
            yield null;
            return;
        }

        // Build query
        $qb = $page->getListSql()
            ->resetOrderBy()
            ->andWhere($page->arrayToFilter($criteria))
            ->setMaxResults(1);
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata($entityClass, 'e');

        // Map computed/custom fields if present
        if (method_exists($page, 'getFieldDefinitions')) {
            foreach ($page->getFieldDefinitions() as $column => $def) {
                if (($def['IsCustom'] ?? false) && isset($def['Expression'])) {
                    $property = GetFieldName($meta, $column);
                    $rsm->addScalarResult($column, $property);
                }
            }
        }
        $query = $em->createNativeQuery($qb, $rsm);
        $result = $query->getOneOrNullResult();
        if ($result === null) {
            yield null; // Stop chain, controller receives null
            return;
        }
        if (is_array($result)) {
            $entity = $result[0] ?? null;
            if (!$entity) {
                yield null;
                return;
            }
            foreach ($result as $key => $value) {
                if ($key === 0) continue;
                if ($entity instanceof \ArrayAccess) {
                    $entity[$key] = $value;
                }
            }
            yield $entity;
            return;
        }
        yield $result;
    }
}
