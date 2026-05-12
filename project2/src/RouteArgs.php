<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\RequestStack;

class RouteArgs implements \ArrayAccess
{
    /**
     * @var array Stores the route parameters as key-value pairs.
     */
    private array $routeParameters;

    /**
     * Constructor
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        // Get the current request and extract route parameters
        $currentRequest = $requestStack->getCurrentRequest();
        $this->routeParameters = $currentRequest ? $currentRequest->attributes->get('_route_params', []) : [];
    }

    /**
     * Get a route parameter by name
     *
     * @param string $name
     * @param mixed $default Default value to return if the parameter does not exist.
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->routeParameters[$name] ?? $default;
    }

    /**
     * Check if a parameter exists
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->routeParameters);
    }

    /**
     * Set or update a route parameter
     *
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, $value): void
    {
        $this->routeParameters[$name] = $value;
    }

    /**
     * ArrayAccess: Check if an offset exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * ArrayAccess: Get a value by offset
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * ArrayAccess: Set a value at the given offset
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * ArrayAccess: Unset a value at the given offset
     *
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->routeParameters[$offset]);
    }

    /**
     * Convert the route parameters to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->routeParameters;
    }

    /**
     * Get an iterator for the route parameters
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->routeParameters);
    }

    /**
     * Add multiple route parameters
     *
     * @param array $parameters
     */
    public function add(array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }
}
