<?php

namespace PHPMaker2026\Project1\Command;

use PHPMaker2026\Project1\LanguageCacheWarmer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:cache:warmup-language',
    description: 'Warms up the language cache'
)]
class WarmupLanguageCacheCommand
{

    public function __construct(
        private readonly LanguageCacheWarmer $languageCacheWarmer,
        #[Autowire('%kernel.cache_dir%')] private readonly string $cacheDir
    ) {
    }

    public function __invoke(OutputInterface $output): int
    {
        $this->languageCacheWarmer->warmUp($this->cacheDir);
        $output->writeln('Language cache warmed up');
        return Command::SUCCESS;
    }
}
