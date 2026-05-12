<?php

namespace PHPMaker2026\Project1;

use DiDom\Element;

/**
 * List option class
 */
class ListOption
{
    public bool $OnLeft = false;
    public string $CssStyle = "";
    public string $CssClass = "";
    public bool $Visible = true;
    public string $Header = "";
    public string $Body = "";
    public string $Footer = "";
    public ?ListOptions $Parent = null;
    public bool $ShowInButtonGroup = true;
    public bool $ShowInDropDown = true;
    public string $ButtonGroupName = "_default";

    // Constructor
    public function __construct(
        public readonly string $Name,
        array $properties = []
    ) {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    // Add a link
    public function addLink(string|array|Attributes $attrs, string $phraseId): void
    {
        $this->Body .= GetLinkHtml($attrs, $phraseId);
    }

    // Clear
    public function clear(): void
    {
        $this->Body = "";
    }

    // Move to
    public function moveTo(int $pos): void
    {
        $this->Parent->moveItem($this->Name, $pos);
    }

    // Get body
    public function getBody(): string
    {
        return $this->Body;
    }

    // Set body
    public function setBody(string $value): static
    {
        $this->Body = $value;
        return $this;
    }

    // Get visible
    public function getVisible(): bool
    {
        return $this->Visible;
    }

    // Set visible
    public function setVisible(bool $value): static
    {
        $this->Visible = $value;
        return $this;
    }

    // Render
    public function render(string $part, int $colspan, string $pos): string
    {
        $tagclass = $this->Parent->TagClassName;
        $td = SameText($this->Parent->Tag, "td");
        if ($part == "header") {
            $tagclass = !IsEmpty($tagclass) ? $tagclass : "ew-list-option-header";
            $value = $this->Header;
        } elseif ($part == "body") {
            $tagclass = !IsEmpty($tagclass) ? $tagclass : "ew-list-option-body";
            $value = $this->Body;
        } elseif ($part == "footer") {
            $tagclass = !IsEmpty($tagclass) ? $tagclass : "ew-list-option-footer";
            $value = $this->Footer;
        } else {
            $value = $part;
        }
        if (strval($value) == "" && preg_match('/inline/', $this->Parent->TagClassName) && $this->Parent->TemplateId == "") { // Skip for multi-column inline tag
            return "";
        }
        $res = $value;
        $attrs = new Attributes(["class" => $tagclass, "data-name" => $this->Name]);
        if ($this->CssStyle) {
            $attrs["style"] = $this->CssStyle;
        }
        $attrs->appendClass($this->CssClass);
        if ($td && in_array($this->Name, [$this->Parent->GroupOptionName, "checkbox"])) { // "button" and "checkbox" columns
            $attrs->appendClass("w-1");
        }
        if ($td && $this->Parent->RowSpan > 1) {
            $attrs["rowspan"] = $this->Parent->RowSpan;
        }
        if ($td && $colspan > 1) {
            $attrs["colspan"] = $colspan;
        }
        $name = $this->Parent->TableVar . "_" . $this->Name;
        if ($this->Name != $this->Parent->GroupOptionName) {
            if (!in_array($this->Name, ["checkbox", "rowcnt"])) {
                if ($this->Parent->UseButtonGroup && $this->ShowInButtonGroup) {
                    $res = $this->Parent->renderButtonGroup($res, $pos);
                    if ($this->OnLeft && $td && $colspan > 1) {
                        $res = '<div class="text-end">' . $res . '</div>';
                    }
                }
            }
            if ($part == "header") {
                $res = '<span id="elh_' . $name . '" class="' . $name . '">' . $res . '</span>';
            } elseif ($part == "body") {
                $res = '<span id="el' . $this->Parent->RowCnt . '_' . $name . '" class="' . $name . '">' . $res . '</span>';
            } elseif ($part == "footer") {
                $res = '<span id="elf_' . $name . '" class="' . $name . '">' . $res . '</span>';
            }
        }
        $tag = ($td && $part == "header") ? "th" : $this->Parent->Tag;
        if ($this->Parent->UseButtonGroup && $this->ShowInButtonGroup) {
            $attrs->appendClass("text-nowrap");
        }
        if ($tag) {
            $res = Element::create($tag, attributes: $attrs->toArray(true))->setInnerHtml($res ?? "")->toDocument()->format()->html();
        }
        if ($this->Parent->TemplateId != "" && $this->Parent->TemplateType == "single") {
            if ($part == "header") {
                $res = '<template id="tpoh_' . $this->Parent->TemplateId . '_' . $this->Name . '">' . $res . '</template>';
            } elseif ($part == 'body') {
                $res = '<template id="tpob' . $this->Parent->RowCnt . '_' . $this->Parent->TemplateId . '_' . $this->Name . '">' . $res . '</template>';
            } elseif ($part == 'footer') {
                $res = '<template id="tpof_' . $this->Parent->TemplateId . '_' . $this->Name . '">' . $res . '</template>';
            }
        }
        return $res;
    }
}
