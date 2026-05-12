<?php

namespace PHPMaker2026\Project1;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Slim\Http\Response as SlimResponse;

class PsrResponseValueResolver implements ValueResolverInterface
{
    /**
     * Constructor
     *
     * @param HttpMessageFactoryInterface $httpMessageFactory
     */
    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * Resolves the PSR-7 Response object
     *
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== ResponseInterface::class) {
            // If the argument type is not ResponseInterface, return an empty iterable
            return [];
        }

        // Create the Slim HTTP Response
        $psrResponse = new SlimResponse($this->responseFactory->createResponse(), $this->streamFactory);

        // Yield the PSR-7 Response object
        yield $psrResponse;
    }
}
