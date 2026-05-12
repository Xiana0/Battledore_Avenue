<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Finder\Finder;
use Psr\Log\LoggerInterface;

class LanguageCacheWarmer implements CacheWarmerInterface
{

    public function __construct(
        protected readonly Language $language,
        protected readonly LoggerInterface $logger,
        protected readonly string $langFolder,
    ) {
    }

    public function isOptional(): bool
    {
        return false; // Always run in prod cache warmup
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $finder = new Finder();
        $finder->files()->in($this->langFolder)->name('*.xml');
        $languages = [];
        foreach ($finder as $file) {
            if (preg_match('/\.([^.]+)\.xml$/', $file->getFilename(), $m)) {
                $languages[$m[1]] = true;
            }
        }
        foreach (array_keys($languages) as $langId) {
            try {
                $this->language->loadLanguage($langId);
            } catch (\Throwable $e) {
                $this->logger->error(
                    sprintf('Error warming up language "%s": %s', $langId, $e->getMessage()),
                    ['exception' => $e]
                );
            }
        }
        return [];
    }
}
