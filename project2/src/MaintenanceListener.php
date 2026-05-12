<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

/**
 * Maintenance Listener
 */
class MaintenanceListener
{
    const RETRY_AFTER = "Retry-After";
    protected bool $enabled = false;
    protected array $ips = [];
    protected int $status = 503;
    protected mixed $retryAfter = null;
    protected string $template = '';

    /**
     * Constructor
     *
     * @param array $options
     * @param KernelInterface $kernel
     * @param PhpRenderer $view
     * @param Environment $twig
     */
    public function __construct(
        array $options,
        protected PhpRenderer $view,
        protected Environment $twig,
        protected Language $language
    ) {
        $this->enabled = $options['enabled'] ?? false;
        $this->ips = $options['ips'] ?? [];
        $this->status = $options['status'] ?? 503;
        $this->retryAfter = $options['retryAfter'];
        $this->template = $options['template'];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        if ($this->enabled && !IpUtils::checkIp($request->getClientIp(), $this->ips)) {
            $viewData = [
                'title' => $this->language->phrase($this->status, true),
                'status' => $this->status,
                'description' => $this->language->phrase($this->status . 'Desc'),
            ];

            // Render view
            if (str_ends_with($this->template, '.php') && $this->view->templateExists($this->template)) {
                $this->view->setAttributes($viewData);
                $response = $this->view->render(new Response('', $this->status), $this->template);
            } elseif (str_ends_with($this->template, '.html.twig') && $this->twig->getLoader()->exists($this->template)) {
                $content = $this->twig->render($this->template, $viewData);
                $response = new Response($content, $this->status);
            } else { // Fallback to plain text response
                $response = new Response($this->language->phrase($this->status, true), $this->status);
            }

            // Set Retry-After header
            if (is_int($this->retryAfter)) {
                $response->headers->set(self::RETRY_AFTER, (string) $this->retryAfter);
            } elseif ($this->retryAfter instanceof DateTimeInterface) {
                $response->headers->set(self::RETRY_AFTER, $this->retryAfter->format('D, d M Y H:i:s \G\M\T'));
            }
            $event->setResponse($response);
        }
    }
}
