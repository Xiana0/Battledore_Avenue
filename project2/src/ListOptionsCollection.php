<?php

namespace PHPMaker2026\Project1;

use ArrayObject;

/**
 * ListOptions collection
 */
class ListOptionsCollection extends ArrayObject
{
    // Constructor
    public function __construct(array $array = [])
    {
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }

    // Render
    public function render(string $part, string $pos = ""): string
    {
        $html = "";
        foreach ($this as $options) {
            $html .= $options->render($part, $pos);
        }
        return $html;
    }

    // Hide all options
    public function hideAllOptions(): void
    {
        foreach ($this as $options) {
            $options->hideAllOptions();
        }
    }

    // Visible
    public function visible(): bool
    {
        return array_any($this->getArrayCopy(), fn($options) => $options->visible());
    }
}
