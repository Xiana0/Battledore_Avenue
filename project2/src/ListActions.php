<?php

namespace PHPMaker2026\Project1;

/**
 * List actions class
 */
class ListActions implements \ArrayAccess, \IteratorAggregate
{
    public array $Items = [];

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
        $item = $this->Items[$offset] ?? null;
        return $item;
    }

    // Implements IteratorAggregate
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->Items);
    }

    // Add and return a new action
    public function add(
        string|array|ListAction $action, // Name
        string $caption = "", // Caption
        bool $allowed = true,
        string $method = ActionType::POSTBACK,
        string $select = ActionType::MULTIPLE,
        string $confirmMessage = "",
        string $icon = "fa-solid fa-star ew-icon",
        string $success = "",
        mixed $handler = null,
        string $successMessage = "",
        string $failureMessage = ""): ListAction|array
    {
        if (is_array($action)) {
            foreach ($action as $item) {
                if ($item instanceof ListAction) {
                    $this->Items[$item->Action] = $item;
                }
            }
            return $action;
        } elseif ($action instanceof ListAction) {
            $this->Items[$action->Action] = $action;
            return $action;
        }
        $item = new ListAction($action, $caption, $allowed, $method, $select, $confirmMessage, $icon, $success, $handler, $successMessage, $failureMessage);
        $this->Items[$action] = $item;
        return $item;
    }
}
