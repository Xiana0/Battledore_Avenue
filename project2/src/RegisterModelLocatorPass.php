<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * Register model locator pass
 */
class RegisterModelLocatorPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('app.internal_locator')) {
            return;
        }
        $locatorMap = [];
        $internalLocator = $container->getDefinition('app.internal_locator');
        $existingMap = $internalLocator->getArgument(0);
        foreach ($existingMap as $key => $ref) {
            $locatorMap[$key] = $ref;
        }

        // Process tagged services with class reflection
        foreach ($container->findTaggedServiceIds('app.model') as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();
            if (!$class || !class_exists($class)) {
                continue;
            }

            // Add class name as key
            if (!isset($locatorMap[$class])) {
                $locatorMap[$class] = new Reference($serviceId);
            }

            // Read #[AsAlias] attributes and add aliases as keys
            $reflection = new \ReflectionClass($class);
            foreach ($reflection->getAttributes(AsAlias::class) as $attr) {
                $alias = $attr->newInstance()->id ?? null;
                if ($alias && !isset($locatorMap[$alias])) {
                    $locatorMap[$alias] = new Reference($serviceId);
                }
            }
        }
        $internalLocator->setArgument(0, $locatorMap);
    }
}
