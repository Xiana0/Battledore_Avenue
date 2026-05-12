<?php

namespace PHPMaker2026\Project1;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator;

/**
 * Services Configuration Event
 */
class ServicesConfigurationEvent extends Event
{

    public function __construct(protected AbstractConfigurator $servicesConfigurator)
    {
    }

    public function getServices(): AbstractConfigurator
    {
        return $this->servicesConfigurator;
    }

    public function getSubject(): AbstractConfigurator
    {
        return $this->servicesConfigurator;
    }
}
