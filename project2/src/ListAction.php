<?php

namespace PHPMaker2026\Project1;

use PHPMaker2026\Project1\Entity as BaseEntity;

/**
 * List action class
 */
class ListAction
{
    protected Language $language;
    protected array $data = []; // Extra data
    protected ?DbFields $fields = null; // Fields
    protected string $url = ""; // URL (empty string by default => current page)
    protected bool $enabled = true;
    protected ?bool $visible = null;

    // Constructor
    public function __construct(
        public string $Action,
        public string $Caption = "",
        public bool $Allowed = true,
        public ActionType $Method = ActionType::POSTBACK, // Postback (P) / Redirect (R) / Ajax (A)
        public ActionType $Select = ActionType::MULTIPLE, // Multiple (M) / Single (S) / Custom (C)
        public string|array $ConfirmMessage = "", // Message or Swal config
        public string $Icon = "fa-solid fa-star ew-icon", // Icon
        public string $Success = "", // JavaScript callback function name (not supported by ActionType::REDIRECT)
        public mixed $Handler = null, // PHP callable to handle the action
        public string $SuccessMessage = "", // Default success message
        public string $FailureMessage = "", // Default failure message
    ) {
        $this->language = Language();
    }

    // Create a new instance
    public static function create(): static
    {
        return new static();
    }

    // Get all data
    public function getAllData(): array
    {
        return $this->data;
    }

    // Get data
    public function getData(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    // Set data
    public function setData(mixed ...$args): static
    {
        $numargs = count($args);
        if ($numargs == 1 && is_array($args[0])) {
            foreach ($args[0] as $key => $value) {
                $this->data[$key] = $value;
            }
        } elseif ($numargs == 2) {
            $this->data[$args[0]] = $args[1];
        }
        return $this;
    }

    // Remove data
    public function removeData(string $key): void
    {
        unset($this->data[$key]);
    }

    // Get URL
    public function getUrl(): string
    {
        return $this->url;
    }

    // Set URL
    public function setUrl(string $value): static
    {
        $this->url = $value;
        return $this;
    }

    // Get caption (virtual)
    public function getCaption(): string
    {
        return $this->Caption ?: $this->Action;
    }

    // Set caption
    public function setCaption(?string $value): static
    {
        $this->Caption = $value;
        return $this;
    }

    // Get visible
    public function getVisible(): bool
    {
        return $this->visible ?? $this->Allowed;
    }

    // Set visible
    public function setVisible(bool $value): static
    {
        $this->visible = $value;
        return $this;
    }

    // Get enabled
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    // Set enabled
    public function setEnabled(bool $value): static
    {
        $this->enabled = $value;
        return $this;
    }

    // Get fields
    public function getFields(): DbFields
    {
        return $this->fields;
    }

    // Set fields (virtual)
    public function setFields(DbFields $value): static
    {
        $this->reset();
        $this->fields = $value;
        return $this;
    }

    // Reset
    public function reset(): void
    {
        $this->data = [];
        $this->enabled = true;
        $this->visible = null;
    }

    // Handle action
    public function handle(BaseEntity $row, PageInterface $listPage): bool
    {
        if (is_callable($this->Handler)) {
            $handler = $this->Handler;
            return $handler($row, $listPage);
        }
        return true;
    }

    // To JSON
    public function toJson(bool $htmlEncode = false): string
    {
        $json = json_encode([
            "msg" => $this->ConfirmMessage,
            "action" => $this->Action,
            "method" => $this->Method->value,
            "select" => $this->Select->value,
            "success" => $this->Success,
        ] + ($this->url ? ["url" => $this->url] : []) + ($this->data ? ["data" => $this->data] : []));
        return $htmlEncode ? HtmlEncode($json) : $json;
    }

    // To data-* attributes
    public function toDataAttributes(): string
    {
        return (string)Attributes::create([
            "data-msg" => HtmlEncode($this->ConfirmMessage),
            "data-action" => HtmlEncode($this->Action),
            "data-method" => $this->Method->value,
            "data-select" => $this->Select->value,
            "data-success" => HtmlEncode($this->Success),
        ] + ($this->url ? ["data-url" => HtmlEncode($this->url)] : []) + ($this->data ? ["data-data" => HtmlEncode(json_encode($this->data))] : []));
    }
}
