<?php

namespace PHPMaker2026\Project1\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:recipes',
    aliases: ['app:recipes:install', 'app:recipes:update'],
    description: 'Proxy for Composer Flex recipe commands (show, install, update) with Flex auto-toggle.'
)]
class RecipesCommand extends Command
{

    protected function configure(): void
    {
        // Allow arbitrary extra arguments like --force, -v, etc.
        $this->ignoreValidationErrors();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $invoked = $_SERVER['argv'][1] ?? 'app:recipes';
        $composerCmd = match (true) {
            str_contains($invoked, 'app:recipes:install') => 'recipes:install',
            str_contains($invoked, 'app:recipes:update')  => 'recipes:update',
            default                                       => 'recipes',
        };
        $args = array_slice($_SERVER['argv'], 2);
        if ($this->requiresExplicitPackage($composerCmd) && !$this->hasPackageArgument($args)) {
            $output->writeln('<error>You must specify at least one package name, e.g. "symfony/asset-mapper".</error>');
            return Command::FAILURE;
        }

        // For recipes:update, only allow one package at a time (Composer limitation)
        if ($composerCmd === 'recipes:update' && count($args) > 1) {
            foreach ($args as $package) {
                if (str_starts_with($package, '-')) continue;
                $composer = $this->buildComposerCommand($composerCmd, [$package]);
                $output->writeln("<info>Running:</info> $composer\n");
                $result = $this->executeComposerCommand($composer, $output);
                if ($result !== Command::SUCCESS) {
                    return $result;
                }
            }
            return Command::SUCCESS;
        }
        $composer = $this->buildComposerCommand($composerCmd, $args);
        $output->writeln("<info>Running:</info> $composer\n");
        return $this->executeComposerCommand($composer, $output);
    }

    /**
     * Execute a Composer recipes command while filtering noisy output.
     */
    protected function executeComposerCommand(string $composer, OutputInterface $output): int
    {
        try {
            $this->toggleFlex(true, $output);
            $process = Process::fromShellCommandline($composer);
            $process->setTimeout(null);
            $process->run(function ($type, $buffer) use ($output) {
                foreach (explode("\n", $buffer) as $line) {
                    $plainLine = preg_replace('/\x1B\[[0-9;]*m/', '', $line);
                    $trimmed = trim($plainLine);
                    if ($trimmed === '') {
                        continue;
                    }

                    // Only skip the single add-lines patching line for base.html.twig
                    if (preg_match('#^\[add-lines\]\s+Patching file\s+"templates[\\/]base\.html\.twig"#i', $trimmed)) {
                        continue;
                    }
                    if (preg_match(
                        '/^(Reading|Loading|Checked|Running|Executing|Writing|Generating|Installing|Skipped|Downloading|Fetching|\[\d{3}\])/i',
                        $trimmed
                    )) {
                        continue;
                    }
                    $output->writeln($line);
                }
            });
            if (!$process->isSuccessful()) {
                $output->writeln("\n<error>Command failed.</error>");
                return Command::FAILURE;
            }
            $this->fixTwigComponentNamespace($output);
            return Command::SUCCESS;
        } catch (\Throwable $ex) {
            $output->writeln("<error>{$ex->getMessage()}</error>");
            return Command::FAILURE;
        } finally {
            $this->toggleFlex(false, $output);
        }
    }

    private function requiresExplicitPackage(string $composerCmd): bool
    {
        return in_array($composerCmd, ['recipes:install', 'recipes:update'], true);
    }

    private function hasPackageArgument(array $args): bool
    {
        foreach ($args as $arg) {
            if (!str_starts_with($arg, '-')) {
                return true;
            }
        }
        return false;
    }

    private function buildComposerCommand(string $composerCmd, array $args): string
    {
        $argsString = implode(' ', array_map(static function ($arg) {
            return preg_match('/[\s"\']/', $arg) ? escapeshellarg($arg) : $arg;
        }, $args));
        return trim(sprintf(
            'composer %s --ansi%s',
            $composerCmd,
            $argsString ? ' ' . $argsString : ''
        ));
    }

    private function toggleFlex(bool $enable, OutputInterface $output): void
    {
        $value = $enable ? 'true' : 'false';
        $process = new Process(['composer', 'config', 'allow-plugins.symfony/flex', $value]);
        $process->disableOutput();
        $process->setTimeout(10);
        $process->run();
        if (!$process->isSuccessful()) {
            $output->writeln(sprintf(
                '<error>Warning: Failed to %s symfony/flex.</error>',
                $enable ? 'enable' : 'restore'
            ));
        }
    }

    /**
     * Replace "App\" with current project namespace in twig_component.yaml.
     */
    protected function fixTwigComponentNamespace(OutputInterface $output): void
    {
        $file = dirname(__DIR__, 2) . '/config/packages/twig_component.yaml';
        if (!is_file($file)) {
            return;
        }
        $projectNamespace = preg_replace('/\\\\Command$/', '', __NAMESPACE__);
        $contents = file_get_contents($file);
        if (strpos($contents, 'App\\') === false) {
            return;
        }
        $updated = str_replace('App\\', $projectNamespace . '\\', $contents);
        if ($updated !== $contents) {
            file_put_contents($file, $updated);
            $output->writeln("<comment>Updated twig_component.yaml namespace to:</comment> $projectNamespace\\");
        }
    }
}
