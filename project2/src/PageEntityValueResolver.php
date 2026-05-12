<?php

namespace PHPMaker2026\Project1;

/**
 * Page entity value resolver
 * Resolver to load entity for view/edit paging
 */
use Psr\Container\ContainerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PageEntityValueResolver implements ValueResolverInterface
{

    public function __construct(
        private ManagerRegistry $doctrine,
        private ContainerInterface $container,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $entityClass = $argument->getType();
        if (
            !$entityClass ||
            !class_exists($entityClass) ||
            !is_subclass_of($entityClass, Entity::class) ||
            !$request->query->has(Config('TABLE_PAGE_NUMBER')) ||
            $request->query->has(Config("TABLE_PAGER_TABLE_NAME"))
        ) {
            return [];
        }
        $routeName = $request->attributes->get('_route', '');
        if (
            !$routeName ||
            (!str_starts_with($routeName, 'view.') && !str_starts_with($routeName, 'edit.'))
        ) {
            return [];
        }
        $pageParam = max(1, $request->query->getInt(Config('TABLE_PAGE_NUMBER'), 1));
        $pageName = RouteNameToPageName($routeName);
        if (!$this->container->has($pageName)) {
            return [];
        }
        $page = $this->container->get($pageName);
        if (
            !method_exists($page, 'getListSql') ||
            (str_starts_with($routeName, 'view.') && (!property_exists($page, 'ViewPaging') || !$page->ViewPaging)) ||
            (str_starts_with($routeName, 'edit.') && (!property_exists($page, 'EditPaging') || !$page->EditPaging))
        ) {
            return [];
        }
        $em = $this->doctrine->getManagerForClass($entityClass);
        if (!$em) {
            return [];
        }
        $meta = $em->getClassMetadata($entityClass);
        $sql = $page->getListSql()->setFirstResult($pageParam - 1)->setMaxResults(1);
        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata($entityClass, 'e');
        if (method_exists($page, 'getFieldDefinitions')) {
            $fieldDefs = $page->getFieldDefinitions();
            foreach ($fieldDefs as $column => $def) {
                if (($def['IsCustom'] ?? false) && isset($def['Expression'])) {
                    $rsm->addScalarResult($column, $column); // Use column name for using $entity->set() later
                }
            }
        }
        $query = $em->createNativeQuery($sql, $rsm);
        $result = $query->getOneOrNullResult();
        if (is_array($result) && isset($result[0]) && is_object($result[0])) {
            $entity = $result[0];
            foreach ($result as $key => $value) {
                // Skip the actual entity
                if ($key === 0) {
                    continue;
                }

                // Dynamically attach the custom fields
                $entity->set($key, $value);
            }
            yield $entity;
            return;
        }
        yield $result;
    }
}
