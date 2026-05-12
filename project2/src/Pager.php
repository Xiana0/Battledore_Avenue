<?php

namespace PHPMaker2026\Project1;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\View\ViewInterface;
use RuntimeException;

class Pager extends AbstractPager
{
    protected Pagerfanta $pager;
    protected array $options;
    protected $routeGenerator; // PHP 8.4 disallows callable as a typed property

    public function __construct(
        public string $Url,
        public int $FromIndex,
        public int $CurrentIndex,
        public int $PageSize,
        public int $RecordCount,
        public string $PageSizes = "",
        public string $ContextClass = "",
        public bool $UseAjaxActions = false,
        public array $PagerOptions = [],
        ?bool $autoHidePager = null,
        ?bool $autoHidePageSizeSelector = null,
        ?bool $usePageSizeSelector = null,
    ) {
        parent::__construct(
            $Url,
            $FromIndex,
            $CurrentIndex,
            $PageSize,
            $RecordCount,
            $PageSizes,
            $ContextClass,
            $UseAjaxActions,
            $autoHidePager,
            $autoHidePageSizeSelector,
            $usePageSizeSelector
        );
        $adapter = new NullAdapter($RecordCount);
        $this->pager = new Pagerfanta($adapter);
        if ($PageSize < 1) {
            $this->pager->setMaxPerPage($RecordCount);
        } else {
            $this->pager->setMaxPerPage($PageSize);
        }
        $this->pager->setCurrentPage(min($this->pager->getNbPages(), $CurrentIndex));
        $this->routeGenerator = function ($page) use ($Url) {
            $separator = (strpos($Url, '?') !== false) ? '&' : '?';
            return GetUrl($Url) . $separator . Config('TABLE_PAGE_NUMBER') . '=' . $page;
        };
        $this->options = array_merge(
            [
                'route_generator' => $this->routeGenerator,
                'view_class' => BootstrapPagerView::class, // Default view class
                'pager_template' => '<div class="%s"><nav aria-label="Pager">%s</nav></div>',
                'css_pager_class' => 'ew-pager',
            ],
            $this->PagerOptions,
            ['use_ajax' => $this->UseAjaxActions],
        );
    }

    public function render(): string
    {
        // Auto hide pager
        if ($this->AutoHidePager && $this->RecordCount <= $this->PageSize) {
            return parent::render(); // Render page size selector
        }
        $viewClass = $this->options['view_class'];
        if (!class_exists($viewClass)) {
            throw new RuntimeException("Pager view class {$viewClass} does not exist.");
        }
        $view = new $viewClass();
        if (!$view instanceof ViewInterface) {
            throw new RuntimeException("Pager view class {$viewClass} is not instance of \Pagerfanta\View\ViewInterfaceViewInterface.");
        }
        $html = $view->render($this->pager, $this->routeGenerator, $this->options);
        if ($this->options['show_dots'] ?? false) {
            $this->options['css_pager_class'] .= ' ew-pager-dots';
        }
        if (($this->options['proximity'] ?? 0) > 0) {
            $this->options['css_pager_class'] .= ' ew-pager-proximity';
        }
        return sprintf($this->options['pager_template'], $this->options['css_pager_class'], $html) . parent::render();
    }

    public function getPager(): Pagerfanta
    {
        return $this->pager;
    }

    public function setPager(Pagerfanta $pager): void
    {
        $this->pager = $pager;
    }

    public function getRouteGenerator(): callable
    {
        return $this->routeGenerator;
    }

    public function setRouteGenerator(callable $routeGenerator): void
    {
        $this->routeGenerator = $routeGenerator;
    }
}
