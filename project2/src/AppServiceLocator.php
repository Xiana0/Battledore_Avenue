<?php

namespace PHPMaker2026\Project1;

use Psr\Container\ContainerInterface;

class AppServiceLocator implements ContainerInterface
{

    public function __construct(
        private readonly ContainerInterface $internalLocator,
        private readonly ContainerInterface $taggedLocator,
    ) {}

    public function get(string $id): mixed
    {
        if ($this->internalLocator->has($id)) {
            return $this->internalLocator->get($id);
        }
        if ($this->taggedLocator->has($id)) {
            return $this->taggedLocator->get($id);
        }
        throw new \RuntimeException("Service '$id' not found in app service locator.");
    }

    public function has(string $id): bool
    {
        return $this->internalLocator->has($id) || $this->taggedLocator->has($id);
    }
}
