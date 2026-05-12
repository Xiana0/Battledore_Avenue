<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Route argument values resolver
 */
class RouteArgsValueResolver implements ValueResolverInterface
{
    /**
     * Constructor
     *
     * @param RequestStack $requestStack
     */
    public function __construct(private RequestStack $requestStack)
    {
    }

    /**
     * Resolves the RouteArgs object
     *
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== RouteArgs::class) {
            // If the argument type is not RouteArgs, return an empty iterable
            return [];
        }

        // Create and yield a RouteArgs instance
        yield new RouteArgs($this->requestStack);
    }
}
