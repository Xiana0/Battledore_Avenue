<?php

namespace PHPMaker2026\Project1;

use Illuminate\Contracts\Support\Htmlable;
use Stringable;

/**
 * Class option values
 */
class OptionValues implements Htmlable, Stringable
{
    public static string $HtmlRenderer = PROJECT_NAMESPACE . "OptionsHtml";

    // Constructor
    public function __construct(public array $Values = [])
    {
    }

    // Add value
    public function add($value): static
    {
        $this->Values[] = $value;
        return $this;
    }

    // Convert to HTML (Note: No return type in interface)
    public function toHtml()
    {
        $fn = OptionValues::$HtmlRenderer;
        return is_callable($fn) ? $fn($this->Values) : (string)$this;
    }

    // Convert to string
    public function __toString(): string
    {
        return implode(Config("OPTION_SEPARATOR"), $this->Values);
    }
}
