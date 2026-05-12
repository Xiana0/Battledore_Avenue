<?php

namespace PHPMaker2026\Project1;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Entity collection resolver
 *
 * Resolver to load entities by POSTed "key_m" values
 */
final class EntityCollectionResolver implements ValueResolverInterface
{

    public function __construct(
        private ManagerRegistry $registry,
        private AppServiceLocator $locator
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== 'array' || $argument->getName() !== 'entities') {
            return []; // Not applicable
        }
        $requestKey = 'key_m';
        $keys = $request->request->all($requestKey);
        if (!is_array($keys)) {
            return [];
        }
        $path = $request->getPathInfo(); // e.g. "/api/product/delete"
        $isApi = str_starts_with($path, '/api/');
        if ($isApi) {
            $table = $request->attributes->get('table');
            if (!$table) {
                throw new \InvalidArgumentException('Missing {table} parameter in route.');
            }
            $obj = $this->locator->get($table);
            if (!isset($obj->EntityClass)) {
                throw new \RuntimeException("Table '$table' must define an 'EntityClass' property.");
            }
            $entityClass = $obj->EntityClass;
        } else {
            $routeName = $request->attributes->get('_route', '');
            if (!$routeName) {
                return [];
            }
            $pageName = RouteNameToPageName($routeName);
            $obj = $this->locator->get($pageName);
            if (!isset($obj->EntityClass)) {
                throw new \RuntimeException("Page '$pageName' must define an 'EntityClass' property.");
            }
            $entityClass = $obj->EntityClass;
        }
        $em = $this->registry->getManagerForClass($entityClass);
        if (!$em) {
            return [];
        }
        $metadata = $em->getClassMetadata($entityClass);
        $idFields = $metadata->getIdentifierColumnNames();
        $separator = Config('COMPOSITE_KEY_SEPARATOR');
        $entities = [];
        if (count($keys) > 1) {
            $entities = $obj->loadEntitiesFromFilter($obj->getFilterFromRecordKeys());
        } elseif (count($keys) === 1) {
            $keyString = $keys[0];
            if (count($idFields) === 1) {
                $id = [$idFields[0] => $keyString];
            } else {
                $parts = explode($separator, $keyString);
                if (count($parts) === count($idFields)) {
                    $id = array_combine($idFields, $parts);
                }
            }
            if (isset($id)) {
                $entity = $obj->loadEntity($id);
                if ($entity) {
                    $entities[] = $entity;
                }
            }
        }
        if (empty($entities)) {
            return [];
        }
        yield $entities;
    }
}
