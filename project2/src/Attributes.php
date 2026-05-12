<?php

namespace PHPMaker2026\Project1;

use ParagonIE\CSPBuilder\CSPBuilder;

/**
 * Attributes class
 */
class Attributes implements \ArrayAccess, \IteratorAggregate, \Stringable
{
    protected CSPBuilder $cspBuilder;
    protected static array $styles = [];

    // Constructor
    public function __construct(protected array $attrs = [])
    {
        $this->cspBuilder = Container(CSPBuilder::class);
    }

    // Create
    public static function create(array $attrs = []): static
    {
        return new static($attrs);
    }

    // offsetSet
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->attrs[] = $value;
        } else {
            $this->attrs[$offset] = $value;
        }
    }

    // offsetExists
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attrs[$offset]);
    }

    // offsetUnset
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attrs[$offset]);
    }

    // offsetGet
    public function offsetGet(mixed $offset): mixed
    {
        return $this->attrs[$offset] ?? ""; // No undefined index
    }

    // getIterator
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->attrs);
    }

    // Append class
    public function appendClass(string $value): void
    {
        $cls = $this->offsetGet("class");
        AppendClass($cls, $value);
        $this->attrs["class"] = trim($cls);
    }

    // Prepend class
    public function prependClass(string $value): void
    {
        $cls = $this->offsetGet("class");
        PrependClass($cls, $value);
        $this->attrs["class"] = trim($cls);
    }

    // Remove class
    public function removeClass(string $value): void
    {
        $cls = $this->offsetGet("class");
        RemoveClass($cls, $value);
        $this->attrs["class"] = trim($cls);
    }

    // Append
    public function append(mixed $offset, mixed $value, string $sep = ""): void
    {
        if (SameText($offset, "class")) {
            $this->appendClass($value);
        }
        $ar = array_unique(array_filter([$this->offsetGet($offset), $value], fn($v) => !IsEmpty($v)));
        $this->attrs[$offset] = implode($sep, $ar);
    }

    // Prepend
    public function prepend(mixed $offset, mixed $value, string $sep = ""): void
    {
        if (SameText($offset, "class")) {
            $this->prependClass($value);
        }
        $ar = array_unique(array_filter([$value, $this->offsetGet($offset)], fn($v) => !IsEmpty($v)));
        $this->attrs[$offset] = implode($sep, $ar);
    }

    // Merge attributes
    public function merge(Attributes|array $attrs): void
    {
        if ($attrs instanceof Attributes) {
            $attrs = $attrs->toArray();
        }
        if (is_array($attrs)) {
            if (isset($attrs["class"])) {
                $this->appendClass($attrs["class"]);
                unset($attrs["class"]);
            }
            $this->attrs = array_replace_recursive($this->attrs, $attrs);
        }
    }

    // Hash
    public function hash(string $directive, string $style): void
    {
        if (!in_array($style, self::$styles)) {
            self::$styles[] = $style;
            $this->cspBuilder->hash($directive, $style);
        }
    }

    // To array
    public function toArray(bool $hash = false): array
    {
        $ar = array_filter($this->attrs, fn($v) => $v !== null);
        if ($hash) {
            if (isset($ar["style"]) && $ar["style"] != "") {
                $this->hash("style-src-attr", $ar["style"]);
            }
        }
        return $ar;
    }

    /**
     * To string
     *
     * @param array $exclude Keys to exclude
     * @return string
     */
    public function toString($exclude = []): string
    {
        $att = "";
        foreach ($this->attrs as $k => $v) {
            $key = trim($k);
            if (in_array($key, $exclude)) {
                continue;
            }
            $v = $v instanceof \UnitEnum ? $v->value : $v; // Convert enum to string
            if (is_array($v)) {
                $v = ArrayToJsonAttribute($v); // Convert array to JSON
            }
            $value = trim($v ?? "");
            if (IsBooleanAttribute($key) && $value !== false) { // Allow boolean attributes, e.g. "disabled"
                $att .= ' ' . $key . (($value != "" && $value !== true) ? '="' . $value . '"' : '');
            } elseif ($key != "" && $value != "") {
                $att .= ' ' . $key . '="' . $value . '"';
                if ($key == "style") {
                    $this->hash("style-src-attr", $value);
                }
            } elseif ($key == "alt" && $value == "") { // Allow alt="" since it is a required attribute
                $att .= ' alt=""';
            }
        }
        return $att;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
