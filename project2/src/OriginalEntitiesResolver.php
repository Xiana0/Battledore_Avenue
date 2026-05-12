<?php

namespace PHPMaker2026\Project1;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Original entities resolver
 *
 * Resolver to load original entities by POSTed "k<n>_oldkey" values
 */
final class OriginalEntitiesResolver implements ValueResolverInterface
{

    public function __construct(
        private ManagerRegistry $registry,
        private AppServiceLocator $locator
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== 'array' || $argument->getName() !== 'entities') {
            return [];
        }
        $path = $request->getPathInfo();
        if (str_starts_with($path, '/api/')) {
            // Only applicable for non-API routes
            return [];
        }
        $routeName = $request->attributes->get('_route', '');
        if (!$routeName) {
            return [];
        }
        $pageName = RouteNameToPageName($routeName);
        if (!$this->locator->has($pageName)) {
            return [];
        }
        $page = $this->locator->get($pageName);
        if (!isset($page->EntityClass)) {
            throw new \RuntimeException("Page '$pageName' must define an 'EntityClass' property.");
        }
        $entityClass = $page->EntityClass;
        $em = $this->registry->getManagerForClass($entityClass);
        if (!$em) {
            return [];
        }
        $metadata = $em->getClassMetadata($entityClass);
        $idFields = $metadata->getIdentifierColumnNames();
        $separator = Config('COMPOSITE_KEY_SEPARATOR');
        $entities = [];
        foreach ($request->request->all() as $key => $value) {
            if (!preg_match('/^k(\d+)_oldkey$/', $key, $matches)) {
                continue;
            }
            $index = (int) $matches[1];
            $keyString = $value;
            if (count($idFields) === 1) {
                $id = [$idFields[0] => $keyString];
            } else {
                $parts = explode($separator, $keyString);
                if (count($parts) !== count($idFields)) {
                    continue;
                }
                $id = array_combine($idFields, $parts);
            }
            $entity = $page->loadEntity($id);
            if ($entity) {
                $entities[$index] = $entity;
            }
        }
        if (empty($entities)) {
            return [];
        }
        yield $entities;
    }
}
