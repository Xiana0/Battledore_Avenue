<?php

namespace PHPMaker2026\Project1;

/**
 * Summary field class
 */
class SummaryField
{
    public string $SummaryCaption = "";
    public ?array $SummaryViewAttrs = null;
    public ?array $SummaryLinkAttrs = null;
    public ?array $SummaryCurrentValues = null;
    public ?array $SummaryViewValues = null;
    public ?array $SummaryValues = null;
    public ?array $SummaryValueCounts = null;
    public ?array $SummaryGroupValues = null;
    public ?array $SummaryGroupValueCounts = null;
    public mixed $SummaryInitValue = null;
    public mixed $SummaryRowSummary = null;
    public int $SummaryRowCount = 0;

    // Constructor
    public function __construct(
        public string $FieldVar, // Field variable name
        public readonly string $Name, // Field name
        public string $Expression, // Field expression (used in SQL)
        public string $SummaryType,
    ) {
    }

    // Summary view attributes
    public function summaryViewAttributes(int $i): string
    {
        if (is_array($this->SummaryViewAttrs)) {
            $attrs = $this->SummaryViewAttrs[$i] ?? null;
            if (is_array($attrs)) {
                return Attributes::create($attrs)->toString();
            }
        }
        return "";
    }

    // Summary link attributes
    public function summaryLinkAttributes(int $i): string
    {
        if (is_array($this->SummaryLinkAttrs)) {
            $attrs = $this->SummaryLinkAttrs[$i] ?? null;
            if (is_array($attrs)) {
                return Attributes::create($attrs)->toString();
            }
        }
        return "";
    }
}
