<?php

namespace PHPMaker2026\Project1;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Route Configuration Event
 */
class RouteConfigurationEvent extends Event
{

    public function __construct(protected RoutingConfigurator $routingConfigurator)
    {
    }

    public function getRoutingConfigurator(): RoutingConfigurator
    {
        return $this->routingConfigurator;
    }

    public function getSubject(): RoutingConfigurator
    {
        return $this->routingConfigurator;
    }
}
