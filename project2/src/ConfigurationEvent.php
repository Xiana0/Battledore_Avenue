<?php

namespace PHPMaker2026\Project1;

use Symfony\Contracts\EventDispatcher\Event;
use Dflydev\DotAccessData\Data;
use Dflydev\DotAccessConfiguration\Configuration;
use Dflydev\DotAccessConfiguration\ConfigurationInterface;

/**
 * Configuration Event
 */
class ConfigurationEvent extends Event
{

    public function __construct(protected Configuration $config)
    {
    }

    public function getConfig(): Configuration
    {
        return $this->config;
    }

    public function getSubject(): Configuration
    {
        return $this->config;
    }

    public function import(array $data, int $mode = Data::REPLACE): void
    {
        $this->config->importRaw($data, $mode);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config->get($key, $default);
    }

    public function set(string $key, mixed $value = null): void
    {
        $this->config->set($key, $value);
    }

    public function append($key, $value = null): void
    {
        $this->config->append($key, $value);
    }
}
