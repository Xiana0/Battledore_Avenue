<?php
declare(strict_types=1);

namespace PHPMaker2026\Project1;

use League\Flysystem\Config;
use League\Flysystem\UrlGeneration\PublicUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Filesystem\Path;

class DynamicPublicUrlGenerator implements PublicUrlGenerator
{

    public function __construct(private RequestStack $requestStack) {}

    /**
     * Generate the public URL for a given path
     */
    public function publicUrl(string $path, Config $config): string
    {
        $publicUrl = $config->get('public_url');
        if (IsRemote($publicUrl)) {
            // Remote storage, use as is
            return Path::join($publicUrl, $path);
        }
        // Determine base URL (runtime)
        $request = $this->requestStack->getCurrentRequest();
        return Path::join($request->getUriForPath(''), $config->get('public_url'), $path);
    }
}
