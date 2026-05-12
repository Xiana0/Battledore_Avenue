<?php

namespace PHPMaker2026\Project1;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\Template\TwitterBootstrap5Template;

class BootstrapPagerTemplate extends TwitterBootstrap5Template
{
    protected Language $language;

    public function __construct(?TemplateInterface $template = null)
    {
        $this->language = Language();
        parent::__construct($template);
    }

    /**
     * Returns default options
     *
     * Inherited options from the parent class include:
     *  - 'prev_message' => 'Previous'
     *  - 'next_message' => 'Next'
     *  - 'dots_message' => '&hellip;'
     *  - 'active_suffix' => '<span class="visually-hidden">(current)</span>'
     *  - 'css_active_class' => 'active'
     *  - 'css_container_class' => 'pagination' // Add pagination-lg or pagination-sm to change size
     *  - 'css_disabled_class' => 'disabled'
     *  - 'css_dots_class' => 'disabled'
     *  - 'css_item_class' => ''
     *  - 'css_prev_class' => ''
     *  - 'css_next_class' => ''
     *  - 'container_template' => '<ul class="%s">%%pages%%</ul>'
     *  - 'rel_previous' => 'prev'
     *  - 'rel_next' => 'next'
     *
     * @return array
     */
    protected function getDefaultOptions(): array
    {
        return [
            ...parent::getDefaultOptions(),
            ...[
                'use_ajax' => false,
                'show_dots' => false,
                'dots_message' => '<span aria-hidden="true"><i class="fa-solid fa-ellipsis ew-icon"></i></span>',
                'first_message' => '<span aria-hidden="true"><i class="fa-solid fa-angles-left ew-icon"></i></span>',
                'last_message' => '<span aria-hidden="true"><i class="fa-solid fa-angles-right ew-icon"></i></span>',
                'prev_message' => '<span aria-hidden="true"><i class="fa-solid fa-angle-left ew-icon"></i></span>',
                'next_message' => '<span aria-hidden="true"><i class="fa-solid fa-angle-right ew-icon"></i></span>',
                'item_template' => '<li class="%s"><a class="page-link h-100" data-url="%s"%s>%s</a></li>',
                'current_template' => '<li class="page-item active" aria-current="page"><input type="text"%s></li>',
                'css_container_class' => 'pagination pagination-sm',
                'css_input_class' => 'form-control form-control-sm border-start-0 rounded-0 position-relative z-3 h-100 ew-page-number',
            ],
        ];
    }

    #[\Override]
    public function pageWithText(int $page, string $text, ?string $attrs = null): string
    {
        return $this->pageWithTextAndClass($page, $text, '', $attrs);
    }

    protected function pageWithTextAndClass(int $page, string $text, string $class, ?string $attrs = null): string
    {
        $attrs = $this->buildDataAttributes('page', ['page' => $page]);
        return $this->linkLi($class, $this->generateRoute($page), $text, $attrs);
    }

    public function previousEnabled(int $page): string
    {
        $attrs = $this->buildDataAttributes('previous', ['page' => $page]);
        return $this->pageWithTextAndClass($page, $this->option('prev_message'), $this->option('css_prev_class'), $attrs);
    }

    public function nextEnabled(int $page): string
    {
        $attrs = $this->buildDataAttributes('next', ['page' => $page]);
        return $this->pageWithTextAndClass($page, $this->option('next_message'), $this->option('css_next_class'), $attrs);
    }

    public function first(): string
    {
        $attrs = $this->buildDataAttributes('first', ['page' => 1]);
        return $this->pageWithText(1, $this->option('first_message'), $attrs);
    }

    public function firstDisabled(): string
    {
        return $this->spanLi($this->option('css_disabled_class'), $this->option('first_message'));
    }

    public function last(int $page): string
    {
        $attrs = $this->buildDataAttributes('last', ['page' => $page]);
        return $this->pageWithText($page, $this->option('last_message'), $attrs);
    }

    public function lastDisabled(): string
    {
        return $this->spanLi($this->option('css_disabled_class'), $this->option('last_message'));
    }

    public function dots(int $page): string
    {
        return $this->pageWithText($page, $this->option('dots_message'));
    }

    #[\Override]
    protected function linkLi(string $class, string $url, $text, ?string $attrs = null): string
    {
        $liClass = implode(' ', array_filter(['page-item', $class]));
        return sprintf($this->option('item_template'), $liClass, $url, $attrs, $text);
    }

    protected function buildDataAttributes(string $type, array $extra = []): string
    {
        $attributes = [];
        if (isset($extra['page'])) {
            $attributes['data-page'] = $extra['page'];
            $attributes['data-ew-action'] = $this->option('use_ajax') ? 'refresh' : 'redirect';
        } else {
            $attributes['data-ew-action'] = 'none';
        }
        if (in_array($type, ['first', 'previous', 'next', 'last'], true)) {
            $attributes['data-value'] = $type;
            $attributes['aria-label'] = $this->language->phrase('Pager' . ucfirst($type));
        }
        foreach ($extra as $key => $value) {
            $attributes[$key] = $value;
        }
        return ' ' . implode(' ', array_map(fn($k, $v) => sprintf('%s="%s"', $k, $v), array_keys($attributes), $attributes));
    }

    #[\Override]
    public function current(int $page): string
    {
        $attrs = $this->buildDataAttributes('current', [
            'data-page' => $page,
            'data-ew-action' => 'change-page',
            'data-ajax' => $this->option('use_ajax') ? 'true' : 'false',
            'data-pagesize' => $this->option('page_size'),
            'data-pagecount' => $this->option('page_count'),
            'name' => 'page',
            'value' => $page,
            'class' => $this->option('css_input_class'),
            'autocomplete' => 'off'
        ]);
        return sprintf($this->option('current_template'), $attrs);
    }
}
