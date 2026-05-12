<?php

namespace PHPMaker2026\Project1\Command;

use Symfony\Bundle\FrameworkBundle\Secrets\AbstractVault;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use function PHPMaker2026\Project1\Config;

#[AsCommand(
    name: 'app:convert-local-secrets',
    description: 'Convert selected secrets from .env.<env>.local into the vault and replace them with placeholders'
)]
final class ConvertLocalEnvToSecretsCommand extends Command
{

    public function __construct(
        #[Autowire(service: 'secrets.vault')] private AbstractVault $vault
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('env', InputArgument::REQUIRED, 'The environment (e.g. dev, prod)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io  = new SymfonyStyle($input, $output);
        $env = $input->getArgument('env');

        // Use current working directory instead of $kernel->getProjectDir()
        $projectDir = getcwd();
        $file = $projectDir . '/.env.' . $env . '.local';
        if (!is_file($file)) {
            $io->error(sprintf('File %s not found', $file));
            return Command::FAILURE;
        }
        $fileContent = file_get_contents($file);
        $dotenv = new Dotenv();
        $vars = $dotenv->parse($fileContent, $file);
        if (!$vars) {
            $io->warning('No environment variables found in file');
            return Command::SUCCESS;
        }
        $io->title(sprintf('Converting selected keys from %s into the vault', $file));

        // Generate vault keys if needed
        if ($this->vault->generateKeys(false)) {
            $io->text('Vault keys generated');
        }
        $updated = false;
        $secretKeys = Config('SECRET_KEYS');
        foreach ($vars as $key => $value) {
            $value = trim($value);

            // Skip empty values
            if ($value === '') {
                continue;
            }

            // Match key patterns
            $match = false;
            foreach ($secretKeys as $pattern) {
                $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/i';
                if (preg_match($regex, $key)) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                continue;
            }
            $io->text(sprintf('Importing %s...', $key));
            $valueToSeal = $value;

            // Handle %kernel.project_dir% and relative paths safely
            if (str_starts_with($value, '%kernel.project_dir%/')) {
                $relativePath = substr($value, strlen('%kernel.project_dir%/'));
                $absolutePath = $projectDir . '/' . $relativePath;
                if (is_file($absolutePath)) {
                    $fileContentToVault = file_get_contents($absolutePath);
                    if ($fileContentToVault !== false) {
                        $valueToSeal = $fileContentToVault;
                        $io->text(sprintf('  -> Loaded content from file %s', $absolutePath));
                        if (@unlink($absolutePath)) {
                            $io->text(sprintf('  -> File %s deleted', $absolutePath));
                        } else {
                            $io->warning(sprintf('  -> Could not delete file %s', $absolutePath));
                        }
                    } else {
                        $io->warning(sprintf('  -> Could not read file %s; storing path as-is', $absolutePath));
                    }
                }
            } else {
                // Only treat as file if it’s a valid local path without scheme
                $scheme = parse_url($value, PHP_URL_SCHEME);
                if (!$scheme && is_file($value)) {
                    $fileContentToVault = file_get_contents($value);
                    if ($fileContentToVault !== false) {
                        $valueToSeal = $fileContentToVault;
                        $io->text(sprintf('  -> Loaded content from file %s', $value));
                        @unlink($value);
                    }
                }
            }

            // Seal new value (overwrites existing secret if present)
            $this->vault->seal($key, $valueToSeal);
            $io->text(sprintf('Secret %s sealed into vault', $key));

            // Replace in .env.<env>.local with placeholder comment
            $patternRegex = '/^' . preg_quote($key, '/') . '\s*=.*$/m';
            $fileContent = preg_replace($patternRegex, $key . '= # Encrypted', $fileContent);
            $updated = true;
        }
        if ($updated) {
            file_put_contents($file, $fileContent);
            $io->success(sprintf('Selected keys in %s replaced with placeholders', $file));
        }
        $io->success('Secrets successfully imported into the vault');
        return Command::SUCCESS;
    }
}
