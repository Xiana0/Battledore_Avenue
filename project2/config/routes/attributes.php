<?php
declare(strict_types=1);

namespace PHPMaker2026\Project1;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import([
        'path' => '../../controllers/',
        'namespace' => __NAMESPACE__,
    ], 'attribute');
    $routingConfigurator->import(PROJECT_NAMESPACE . 'Kernel', 'attribute');
};
