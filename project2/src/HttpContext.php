<?php
declare(strict_types=1);

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Path;
use Dflydev\DotAccessConfiguration\Configuration;
use Dflydev\DotAccessConfiguration\ConfigurationDataSource;
use Dflydev\PlaceholderResolver\RegexPlaceholderResolver;
use Psr\Container\ContainerInterface;
final class HttpContext implements \ArrayAccess
{
    private Request $request;
    private ?Configuration $config = null;
    private EventDispatcherInterface $eventDispatcher;
    private bool $locked = false;
    private ?Kernel $kernel = null;

    /**
     * HttpContext constructor.
     */
    public function __construct()
    {
        $this->request = Request::createFromGlobals();
        $this->eventDispatcher = new EventDispatcher();
        $this->initializeDefaults();
    }

    /**
     * Initialize default application context variables.
     */
    public function initializeDefaults(): void
    {
        // Locale settings
        $this['DECIMAL_SEPARATOR'] = '.';
        $this['GROUPING_SEPARATOR'] = ',';
        $this['CURRENCY_CODE'] = 'USD';
        $this['CURRENCY_SYMBOL'] = '$';
        $this['CURRENCY_FORMAT'] = '¤#,##0.00';
        $this['NUMBER_FORMAT'] = '#,##0.###';
        $this['PERCENT_SYMBOL'] = '%';
        $this['PERCENT_FORMAT'] = '#,##0%';
        $this['DATE_SEPARATOR'] = '/';
        $this['TIME_SEPARATOR'] = ':';
        $this['DATE_FORMAT'] = 'y/MM/dd';
        $this['TIME_FORMAT'] = 'HH:mm';
        $this['TIME_ZONE'] = 'UTC';

        // Global variables
        $this['Conn'] = null;
        $this['Page'] = null;
        $this['Grid'] = null;
        $this['Language'] = null;
        $this['Title'] = null;
        $this['DownloadFileName'] = '';

        // Current language/locale
        $this['CurrentLanguage'] = '';
        $this['CurrentLocale'] = '';

        // Export
        $this['ExportType'] = '';
        $this['ExportId'] = null;

        // Header/footer skip
        $this['SkipHeaderFooter'] = false;
        $this['OldSkipHeaderFooter'] = false;

        // Misc
        $this['TempImages'] = [];
        $this['Nonce'] = '';
        $this['DashboardReport'] = null;
        $this['DrillDownInPanel'] = false;
        $this['ClientVariables'] = [];
        $this['RenderingView'] = false;
        $this['ViewData'] = [];
    }

    /**
     * Add a temporary image to the context.
     *
     * @param string $fileName Temp image file name
     * @return void
     */
    public function addTempImage(string $fileName): void
    {
        $tempImages = $this['TempImages'] ?? [];
        $tempImages[] = $fileName;
        $this['TempImages'] = $tempImages;
    }

    /**
     * Get the Kernel instance.
     */
    public function getKernel(): Kernel
    {
        return $this->kernel;
    }

    /**
     * Set the Kernel instance.
     */
    public function setKernel(Kernel $kernel): void
    {
        $this->kernel = $kernel;
    }

    /**
     * Get the container from the kernel.
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->kernel?->isBooted() ? $this->kernel->getContainer() : null;
    }

    /**
     * Get the current HTTP request.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get the configuration data.
     *
     * @throws \RuntimeException If the config.php file is not found.
     */
    public function getConfig(): Configuration
    {
        if (!$this->config) {
            $configFile = __DIR__ . '/config.php';
            if (!file_exists($configFile)) {
                throw new \RuntimeException("Config file not found: {$configFile}");
            }
            $data = require $configFile;
            $this->config = new Configuration($data);
            $this->config->setPlaceholderResolver(new RegexPlaceholderResolver(new ConfigurationDataSource($this->config), '${', '}'));
            $this->dispatch(new ConfigurationEvent($this->config), ConfigurationEvent::class);
        }
        return $this->config;
    }

    /**
     * Add an event listener.
     */
    public function addListener(string $eventName, callable|array $listener, int $priority = 0): void
    {
        if ($this->kernel?->isBooted()) {
            $mainDispatcher = $this->getContainer()->get('event_dispatcher');
            $mainDispatcher->addListener($eventName, $listener, $priority);
        } else {
            $this->eventDispatcher->addListener($eventName, $listener, $priority);
        }
    }

    /**
     * Dispatch an event.
     */
    public function dispatch(object $event, ?string $eventName = null): object
    {
        if ($this->kernel?->isBooted()) {
            $mainDispatcher = $this->getContainer()->get('event_dispatcher');
            return $mainDispatcher->dispatch($event, $eventName);
        } else {
            return $this->eventDispatcher->dispatch($event, $eventName);
        }
    }

    /**
     * Flush all temporary event listeners to the kernel's event dispatcher.
     *
     * @throws \LogicException if the kernel is not set.
     */
    public function flushEventListeners(): void
    {
        if ($this->locked) {
            return;
        }
        if ($this->kernel === null) {
            throw new \LogicException('Kernel is not set; cannot flush event listeners.');
        }
        $this->locked = true;

        /** @var EventDispatcherInterface $mainDispatcher */
        $mainDispatcher = $this->kernel->getContainer()->get('event_dispatcher');
        $listeners = $this->eventDispatcher->getListeners();
        foreach ($listeners as $eventName => $listenerList) {
            foreach ($listenerList as $listener) {
                $mainDispatcher->addListener($eventName, $listener);
            }
        }
        foreach ($listeners as $eventName => $listenerList) {
            foreach ($listenerList as $listener) {
                $this->eventDispatcher->removeListener($eventName, $listener);
            }
        }
    }

    /**
     * Check if an attribute exists in the request.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->request->attributes->has($offset);
    }

    /**
     * Get an attribute from the request.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->request->attributes->get($offset);
    }

    /**
     * Set an attribute in the request.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->request->attributes->set($offset, $value);
    }

    /**
     * Remove an attribute from the request.
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->request->attributes->remove($offset);
    }

    /**
     * Magic getter for request attributes.
     */
    public function __get(string $name): mixed
    {
        return $this->offsetGet($name);
    }

    /**
     * Magic setter for request attributes.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->offsetSet($name, $value);
    }

    /**
     * Magic isset for request attributes.
     */
    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * Magic unset for request attributes.
     */
    public function __unset(string $name): void
    {
        $this->offsetUnset($name);
    }
}
