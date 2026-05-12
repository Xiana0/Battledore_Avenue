<?php

namespace PHPMaker2026\Project1\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PHPMaker2026\Project1\Language;

class ApiExceptionSubscriber implements EventSubscriberInterface
{

    public function __construct(
        protected ParameterBagInterface $params,
        protected Language $language
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }
        $exception = $event->getThrowable();
        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;
        $message = $this->params->get('kernel.debug')
            ? sprintf(
                "[%s] %s in %s:%d Trace: %s",
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            )
            : ($status === 500 ? $this->language->phrase('500', true) : $exception->getMessage());
        $response = new JsonResponse(['error' => $message], $status);
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        // Note: Earlier than ExceptionListener with priority 1
        return [
            'kernel.exception' => ['onKernelException', 10],
        ];
    }
}
