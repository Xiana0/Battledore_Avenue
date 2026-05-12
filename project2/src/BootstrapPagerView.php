<?php

namespace PHPMaker2026\Project1;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\View;
use Pagerfanta\View\Template\TemplateInterface;
use Pagerfanta\RouteGenerator\RouteGeneratorInterface;

class BootstrapPagerView extends View
{
    protected bool $showDots = false;
    private readonly TemplateInterface $template;

    public function __construct(?TemplateInterface $template = null)
    {
        $this->template = $template ?? $this->createDefaultTemplate();
    }

    /**
     * Create default template
     *
     * @return TemplateInterface
     */
    protected function createDefaultTemplate(): TemplateInterface
    {
        return new BootstrapPagerTemplate();
    }

    /**
     * Render
     *
     * @param PagerfantaInterface<mixed>       $pagerfanta
     * @param callable|RouteGeneratorInterface $routeGenerator
     * @param array<string, mixed>             $options
     */
    public function render(PagerfantaInterface $pagerfanta, callable $routeGenerator, array $options = []): string
    {
        $this->initializePagerfanta($pagerfanta);
        $this->initializeOptions($options);
        $options['page_size'] = $pagerfanta->getMaxPerPage();
        $options['page_count'] = $pagerfanta->getNbPages();
        $this->configureTemplate($routeGenerator, $options);
        $this->showDots = $options['show_dots'] ?? $this->showDots;
        return $this->generate();
    }

    /**
     * Configure template
     *
     * @param callable(int $page): string|RouteGeneratorInterface $routeGenerator
     * @param array<string, mixed>                                $options
     */
    protected function configureTemplate(callable|RouteGeneratorInterface $routeGenerator, array $options): void
    {
        $this->template->setRouteGenerator($routeGenerator);
        $this->template->setOptions($options);
    }

    protected function generate(): string
    {
        return $this->generateContainer($this->generatePages());
    }

    protected function generateContainer(string $pages): string
    {
        return str_replace('%pages%', $pages, $this->template->container());
    }

    protected function generatePages(): string
    {
        $this->calculateStartAndEndPage();
        return $this->first()
            . $this->previous()
            . $this->secondIfStartIs3()
            . $this->dotsIfStartIsOver3()
            . $this->pages()
            . $this->dotsIfEndIsUnder3ToLast()
            . $this->secondToLastIfEndIs3ToLast()
            . $this->next()
            . $this->last();
    }

    protected function previous(): string
    {
        if ($this->pagerfanta->hasPreviousPage()) {
            return $this->template->previousEnabled($this->pagerfanta->getPreviousPage());
        }
        return $this->template->previousDisabled();
    }

    protected function first(): string
    {
        if ($this->startPage > 1) {
            return $this->template->first();
        }
        return $this->template->firstDisabled();
    }

    protected function secondIfStartIs3(): string
    {
        if ($this->proximity < 1) {
            return '';
        }
        if (3 === $this->startPage) {
            return $this->template->page(2);
        }
        return '';
    }

    protected function dotsIfStartIsOver3(): string
    {
        if (!$this->showDots) {
            return '';
        }
        if ($this->startPage > 3) {
            $leftJumpPage = max(1, $this->startPage - ($this->proximity * 2) - 1);
            return $this->template->dots($leftJumpPage);
        }
        return '';
    }

    protected function pages(): string
    {
        \assert(null !== $this->startPage);
        \assert(null !== $this->endPage);
        $pages = '';
        foreach (range($this->startPage, $this->endPage) as $page) {
            $pages .= $this->page($page);
        }
        return $pages;
    }

    protected function page(int $page): string
    {
        if ($page === $this->currentPage) {
            return $this->template->current($page);
        }
        return $this->template->page($page);
    }

    protected function dotsIfEndIsUnder3ToLast(): string
    {
        if (!$this->showDots) {
            return '';
        }
        if ($this->endPage < $this->toLast(3)) {
            $rightJumpPage = min($this->nbPages, $this->endPage + ($this->proximity * 2) + 1);
            return $this->template->dots($rightJumpPage);
        }
        return '';
    }

    protected function secondToLastIfEndIs3ToLast(): string
    {
        if ($this->proximity < 1) {
            return '';
        }
        if ($this->endPage == $this->toLast(3)) {
            return $this->template->page($this->toLast(2));
        }
        return '';
    }

    protected function last(): string
    {
        if ($this->pagerfanta->getNbPages() > $this->endPage) {
            return $this->template->last($this->pagerfanta->getNbPages());
        }
        return $this->template->lastDisabled();
    }

    protected function next(): string
    {
        if ($this->pagerfanta->hasNextPage()) {
            return $this->template->nextEnabled($this->pagerfanta->getNextPage());
        }
        return $this->template->nextDisabled();
    }

    /**
     * Get view name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'bootstrap_pager_view';
    }
}
