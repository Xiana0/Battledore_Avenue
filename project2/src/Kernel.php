<?php

namespace PHPMaker2026\Project1;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Kernel
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    private ?HttpContext $httpContext = null;

    /**
     * Check if the kernel is booted.
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Get the HttpContext instance.
     */
    public function getHttpContext(): HttpContext
    {
        return $this->httpContext ??= new HttpContext();
    }

    /**
     * Set the HttpContext instance manually.
     */
    public function setHttpContext(HttpContext $httpContext): void
    {
        $this->httpContext = $httpContext;
    }

    /**
     * Boot the kernel and flush early listeners.
     */
    public function boot(): void
    {
        parent::boot();
        $context = $this->getHttpContext();
        $context->setKernel($this);
        $context->flushEventListeners();
    }

    /**
     * Build
     *
     * @param ContainerBuilder $container
     * @return void
     */
    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterModelLocatorPass());
        $container->addCompilerPass(new AliasToClassPass());
    }

    /**
     * Configure container imports preferring PHP configs over YAML.
     */
    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $configDir = preg_replace('{/config$}', '/{config}', $this->getConfigDir());
        $this->importWithPhpPreference($container, $configDir.'/{packages}/*');
        $this->importWithPhpPreference($container, $configDir.'/{packages}/'.$this->environment.'/*');
        $this->importWithPhpPreference($container, $configDir.'/services');
        $this->importWithPhpPreference($container, $configDir.'/{services}_'.$this->environment);
    }

    /**
     * Configure routes imports preferring PHP configs over YAML.
     */
    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = preg_replace('{/config$}', '/{config}', $this->getConfigDir());
        $this->importWithPhpPreference($routes, $configDir.'/{routes}/'.$this->environment.'/*');
        $this->importWithPhpPreference($routes, $configDir.'/{routes}/*');
        $this->importWithPhpPreference($routes, $configDir.'/routes');
        if ($fileName = (new \ReflectionObject($this))->getFileName()) {
            $routes->import($fileName, 'attribute');
        }
    }

    /**
     * Import configuration files preferring PHP over YAML counterparts.
     *
     * @param ContainerConfigurator|RoutingConfigurator $configurator
     */
    private function importWithPhpPreference(ContainerConfigurator|RoutingConfigurator $configurator, string $basePath, ?string $type = null): void
    {
        $globFlags = defined('GLOB_BRACE') ? GLOB_BRACE : 0;
        $phpFiles = glob($basePath.'.php', $globFlags) ?: [];
        $yamlFiles = glob($basePath.'.yaml', $globFlags) ?: [];
        foreach ($phpFiles as $file) {
            $configurator->import($file, $type);
        }
        foreach ($yamlFiles as $file) {
            $phpFile = preg_replace('/\.yaml$/', '.php', $file);
            if ($phpFile && is_file($phpFile)) {
                continue;
            }
            $configurator->import($file, $type);
        }
    }
}
