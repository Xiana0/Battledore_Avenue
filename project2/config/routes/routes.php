<?php
declare(strict_types=1);

namespace PHPMaker2026\Project1;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    // Dispatch Route Config event
    $event = new RouteConfigurationEvent($routes);
    DispatchEvent($event, RouteConfigurationEvent::class);
};
