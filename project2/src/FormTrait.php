<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * Form trait
 */
trait FormTrait
{
    protected int $FormIndex = -1;
    protected ?Request $Request = null;
    protected ?bool $jsonRequest = null;

    public function getRequest(): Request
    {
        return $this->Request ??= Request();
    }

    public function isJsonRequest(): bool
    {
        return $this->jsonRequest ??= $this->getRequest()->getContentTypeFormat() === "json";
    }

    public function getFormRowActionName(bool $indexed = false)
    {
        $name = match ($this->PageID) {
            "list" => Config("FORM_ROW_ACTION_NAME"),
            "grid" => Config("FORM_ROW_ACTION_NAME") . "_" . $this->FormName,
            default => ""
        };
        return $indexed ? $this->getFormIndexedName($name) : $name;
    }

    public function getFormBlankRowName(bool $indexed = false)
    {
        $name = match ($this->PageID) {
            "list" => Config("FORM_BLANK_ROW_NAME"),
            "grid" => Config("FORM_BLANK_ROW_NAME") . "_" . $this->FormName,
            default => ""
        };
        return $indexed ? $this->getFormIndexedName($name) : $name;
    }

    public function getFormOldKeyName(bool $indexed = false)
    {
        $name = match ($this->PageID) {
            "grid" => Config("FORM_OLD_KEY_NAME") . "_" . $this->FormName,
            default => Config("FORM_OLD_KEY_NAME")
        };
        return $indexed ? $this->getFormIndexedName($name) : $name;
    }

    public function getFormRowHashName(bool $indexed = false)
    {
        $name = match ($this->PageID) {
            "grid" => Config("FORM_ROW_HASH_NAME") . "_" . $this->FormName,
            default => Config("FORM_ROW_HASH_NAME")
        };
        return $indexed ? $this->getFormIndexedName($name) : $name;
    }

    public function getFormKeyCountName()
    {
        return match ($this->PageID) {
            "list" => Config("FORM_KEY_COUNT_NAME"),
            "grid" => Config("FORM_KEY_COUNT_NAME") . "_" . $this->FormName,
            default => ""
        };
    }

    public function getFormIndexedName(string $name): string
    {
        return preg_match(Config("FORM_HIDDEN_INPUT_NAME_PATTERN"), $name) && $this->FormIndex >= 0
            ? substr($name, 0, 1) . $this->FormIndex . substr($name, 1)
            : $name;
    }

    public function isGridPage(): bool
    {
        return $this->PageID == "grid";
    }

    public function hasFormValue(string $name): bool
    {
        $wrkname = $this->getFormIndexedName($name);
        if ($this->isGridPage() && preg_match(Config("FORM_HIDDEN_INPUT_NAME_PATTERN"), $name)) {
            if ($this->hasParam($this->FormName . '$' . $wrkname)) {
                return true;
            }
        }
        return $this->hasParam($wrkname);
    }

    protected function hasParam(string $name): bool
    {
        return $this->getRequest()->request->has($name);
    }

    public function getFormValue(string $name, mixed $default = ""): mixed
    {
        $wrkname = $this->getFormIndexedName($name);
        if ($this->isGridPage() && preg_match(Config("FORM_HIDDEN_INPUT_NAME_PATTERN"), $name)) {
            if ($this->hasParam($this->FormName . '$' . $wrkname)) {
                return $this->getParam($this->FormName . '$' . $wrkname, $default);
            }
        }
        return $this->getParam($wrkname, $default);
    }

    protected function getParam(string $name, mixed $default = ""): mixed
    {
        try {
            return $this->getRequest()->request->get($name, $default);
        } catch (BadRequestException $e) {
            return $this->getRequest()->request->all()[$name] ?? $default; // Allow array
        }
    }

    public function hasInputValue(DbField $field): bool
    {
        if ($this->isJsonRequest()) { // API
            if ($this->hasParam($field->Name) || $field->PropertyName && $this->hasParam($field->PropertyName)) { // Check field name first for backward compatibility
                return true;
            } else {
                $field->Visible = true; // Disable field for form validation and API update
            }
        } else { // POST
            if ($this->hasFormValue($field->Name) || $this->hasFormValue($field->FieldVar)) { // Check field name first
                return true;
            }
        }
        return false;
    }

    public function getInputValue(DbField $field): mixed
    {
        if ($this->isJsonRequest()) { // API
            if ($this->hasParam($field->Name)) { // Check field name first for backward compatibility
                $field->Visible = true;
                return $this->getParam($field->Name);
            }
            if ($field->PropertyName && $this->hasParam($field->PropertyName)) {
                $field->Visible = true;
                return $this->getParam($field->PropertyName);
            }
        } else { // POST
            if ($this->hasFormValue($field->Name)) { // Check field name first
                return $this->getFormValue($field->Name);
            }
            if ($this->hasFormValue($field->FieldVar)) {
                return $this->getFormValue($field->FieldVar);
            }
        }
        return null;
    }

    public function hasBlankRow(): bool
    {
        return $this->hasFormValue($this->getFormBlankRowName());
    }

    public function hasKeyCount(): bool
    {
        return $this->hasFormValue($this->getFormKeyCountName());
    }

    public function hasRowAction(): bool
    {
        return $this->hasFormValue($this->getFormRowActionName());
    }

    public function getKeyCount(): int
    {
        return $this->getRequest()->request->getInt($this->getFormKeyCountName()); // Name is not indexed
    }

    public function getRowAction(): string
    {
        return $this->getFormValue($this->getFormRowActionName());
    }

    // *** Note: name clash with getOldKey, change to getFormOldKey // PHP
    public function getFormOldKey(): string
    {
        return $this->getFormValue($this->getFormOldKeyName());
    }

    public function getOldRowHash(): string
    {
        return $this->getFormValue($this->getFormRowHashName());
    }

    // Get search value for form element
    public function getSearchValues(string $name): array
    {
        $index = $this->FormIndex;
        $this->FormIndex = -1;
        try {
            return [
                "value" => $this->getFormValue("x_$name"),
                "operator" => $this->getFormValue("z_$name"),
                "condition" => $this->getFormValue("v_$name"),
                "value2" => $this->getFormValue("y_$name"),
                "operator2" => $this->getFormValue("w_$name"),
            ];
        } finally {
            $this->FormIndex = $index;
        }
    }
}
