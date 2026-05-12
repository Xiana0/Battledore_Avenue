<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ReflectionClass;

class AliasToClassPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        $map = [];
        foreach ($container->getAliases() as $aliasName => $alias) {
            $serviceId = (string) $alias;
            if (!$container->hasDefinition($serviceId)) {
                continue;
            }
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();
            if ($class && class_exists($class)) {
                $reflection = new ReflectionClass($class);
                $shortName = $reflection->getShortName();
                $map[$aliasName] = $shortName;
            }
        }
        $container->setParameter('app.alias_to_class_map', $map);
    }
}
