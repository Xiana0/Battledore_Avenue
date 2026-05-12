<?php

namespace PHPMaker2026\Project1;

/**
 * Sub pages class
 */
class SubPages implements \ArrayAccess, \IteratorAggregate
{
    public static string $DEFAULT_STYLE = "tabs";

    // Constructor
    public function __construct(
        public ?string $Style = null, // "tabs" (nav nav-tabs) or "pills" (nav nav-pills) or "underline" (nav nav-underline) or "" (nav) or "accordion"
        public bool $Justified = false,
        public bool $Fill = false,
        public bool $Vertical = false,
        public string $Align = "", // "start" or "center" or "end"
        public string $Classes = "", // Other CSS classes
        public string $Parent = "false", // For the data-bs-parent attribute on each .accordion-collapse
        public array $Items = [],
        public ?array $ValidKeys = null,
        public ?string $ActiveIndex = null
    ) {
        $this->Style ??= self::$DEFAULT_STYLE;
    }

    // Implements offsetSet
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->Items[] = $value;
        } else {
            $this->Items[$offset] = $value;
        }
    }

    // Implements offsetExists
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->Items[$offset]);
    }

    // Implements offsetUnset
    public function offsetUnset(mixed $offset): void
    {
        unset($this->Items[$offset]);
    }

    // Implements offsetGet
    public function offsetGet(mixed $offset): mixed
    {
        return $this->Items[$offset] ?? null;
    }

    // Implements IteratorAggregate
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->Items);
    }

    // Get .nav classes
    public function navClasses(): string
    {
        $style = "nav";
        if (!$this->Vertical) { // Not vertical
            $style .= " nav-" . $this->Style;
            if ($this->Justified) {
                $style .= " nav-justified";
            }
            if ($this->Fill) {
                $style .= " nav-fill";
            }
            if (in_array($this->Align, ["start", "center", "end"])) {
                $style .= " justify-content-" . $this->Align;
            }
        } else { // Vertical
            if ($this->isPills() or $this->isUnderline()) { // Vertical does not support tabs
                $style .= " nav-" . $this->Style;
            }
            $style .= " flex-column me-3";
        }
        if ($this->Classes) {
            $style .= " " . $this->Classes;
        }
        return $style;
    }

    // Get .nav-link classes
    public function navLinkClasses(string $k): string
    {
        $classes = "nav-link";
        if ($this->isActive($k)) { // Active page
            $classes .= " active";
        } else {
            $item = $this->getItem($k);
            if ($item) {
                if (!$item->Visible) {
                    $classes .= " d-none ew-hidden";
                } elseif ($item->Disabled) {
                    $classes .= " disabled ew-disabled";
                }
            }
        }
        return $classes;
    }

    // Check if a page is active
    public function isActive(string $k): bool
    {
        return $this->activePageIndex() == $k;
    }

    // Check if accordion
    public function isAccordion(): bool
    {
        return $this->Style == "accordion";
    }

    // Check if tabs
    public function isTabs(): bool
    {
        return $this->Style == "tabs";
    }

    // Check if pills
    public function isPills(): bool
    {
        return $this->Style == "pills";
    }

    // Check if underline
    public function isUnderline(): bool
    {
        return $this->Style == "underline";
    }

    // Get container classes (for .nav.lex-column)
    public function containerClasses(): string
    {
        return $this->Vertical ? " d-flex align-items-start" : "";
    }

    // Get tab content classes (for .tab-content)
    public function tabContentClasses(): string
    {
        $classes = "tab-content";
        if ($this->Vertical) {
            $classes .= " flex-grow-1";
        }
        return $classes;
    }

    // Get active classes (for .nav-link if tabs/pills or .accordion-collapse if accordion)
    public function activeClasses(string $k): string
    {
        if ($this->isActive($k)) { // Active page
            if ($this->isTabs() || $this->isPills() || $this->isUnderline()) { // Tabs/Pills/Underline
                return " active";
            } elseif ($this->isAccordion()) { // Accordion
                return " show";
            }
        }
        return "";
    }

    // Get .tab-pane classes (for .tab-pane if tabs/pills)
    public function tabPaneClasses(string $k): string
    {
        $classes = "tab-pane fade";
        if ($this->isActive($k)) { // Active page
            $classes .= " active show";
        }
        return $classes;
    }

    // Get count
    public function count(): int
    {
        return count($this->Items);
    }

    // Add item by name
    public function add(string $name = ""): SubPage
    {
        $item = new SubPage();
        if (strval($name) != "") {
            $this->Items[$name] = $item;
        }
        if (!is_int($name)) {
            $this->Items[] = $item;
        }
        return $item;
    }

    // Get item by key
    public function getItem(string $k): ?SubPage
    {
        return $this->Items[$k] ?? null;
    }

    // Set active page index
    public function setActivePageIndex(string $k): bool
    {
        if (($item = $this->getItem($k)) && $item->Visible && !$item->Disabled) {
            $this->ActiveIndex = $k;
            return true;
        }
        return false;
    }

    // Active page index
    public function activePageIndex(): ?string
    {
        if ($this->ActiveIndex !== null) {
            return $this->ActiveIndex;
        }

        // Return first active page
        foreach ($this->Items as $key => $item) {
            if ((!is_array($this->ValidKeys) || in_array($key, $this->ValidKeys)) && $item->Visible && !$item->Disabled && $item->Active && $key !== 0) { // Not common page
                $this->ActiveIndex = $key;
                return $this->ActiveIndex;
            }
        }

        // If not found, return first visible page
        foreach ($this->Items as $key => $item) {
            if ((!is_array($this->ValidKeys) || in_array($key, $this->ValidKeys)) && $item->Visible && !$item->Disabled && $key !== 0) { // Not common page
                $this->ActiveIndex = $key;
                return $this->ActiveIndex;
            }
        }

        // Not found
        return null;
    }
}
