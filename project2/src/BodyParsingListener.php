<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class BodyParsingListener
{

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $content = $request->getContent();
        if ($content === '' || $content === null) {
            return;
        }
        $contentType = $request->headers->get('Content-Type');
        $isJson = $request->getContentTypeFormat() === 'json'
            || (is_string($contentType) && preg_match('/\bjson\b|\+json\b/i', $contentType));
        $trimmed = null;
        if (!$isJson) {
            $trimmed = trim($content);
            $looksLikeJson = $trimmed !== '' && (
                (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}'))
                || (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'))
            );
            if (!$looksLikeJson) {
                return;
            }
        }
        try {
            $data = json_decode($trimmed ?? trim($content), true, 512, JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return;
        }
        if (!is_array($data)) {
            return;
        }
        $request->request->replace($data);
    }
}
