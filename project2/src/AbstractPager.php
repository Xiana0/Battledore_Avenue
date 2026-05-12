<?php

namespace PHPMaker2026\Project1;

/**
 * Abstract pager
 */
abstract class AbstractPager
{
    protected bool $PageSizeAll = false; // Handle page size = -1 (ALL)
    public int $ToIndex = 0;
    public bool $Visible = true;
    public bool $AutoHidePager = true;
    public bool $AutoHidePageSizeSelector = true;
    public bool $UsePageSizeSelector = true;

    // Constructor
    public function __construct(
        public string $Url,
        public int $FromIndex,
        public int $CurrentIndex,
        public int $PageSize,
        public int $RecordCount,
        public string $PageSizes = "",
        public string $ContextClass = "",
        public bool $UseAjaxActions = false,
        ?bool $AutoHidePager = null,
        ?bool $AutoHidePageSizeSelector = null,
        ?bool $UsePageSizeSelector = null
    ) {
        $this->AutoHidePager = $AutoHidePager ?? Config("AUTO_HIDE_PAGER");
        $this->AutoHidePageSizeSelector = $AutoHidePageSizeSelector ?? Config("AUTO_HIDE_PAGE_SIZE_SELECTOR");
        $this->UsePageSizeSelector = $UsePageSizeSelector ?? true;

        // Handle page size = 0
        if ($this->PageSize == 0) {
            $this->PageSize = $this->RecordCount > 0 ? $this->RecordCount : 10;
        }

        // Handle page size = -1 (ALL)
        if (in_array($this->PageSize, [-1, $this->RecordCount], true)) {
            $this->PageSizeAll = true;
            $this->PageSize = $this->RecordCount > 0 ? $this->RecordCount : 10;
        }
        if ($this->FromIndex > $this->RecordCount) {
            $this->FromIndex = $this->RecordCount;
        }
        $this->ToIndex = $this->FromIndex + $this->PageSize - 1;
        if ($this->ToIndex > $this->RecordCount) {
            $this->ToIndex = $this->RecordCount;
        }
    }

    // Is visible
    public function isVisible(): bool
    {
        return $this->RecordCount > 0 && $this->Visible;
    }

    // Render
    public function render(): string
    {
        $html = '';
        if ($this->isVisible() && $this->PageSize > 1 && !($this->AutoHidePager && $this->RecordCount <= $this->PageSize)) {
            $html .= $this->renderRecordInfo();
        }
        if ($this->UsePageSizeSelector && !empty($this->PageSizes) && !($this->AutoHidePageSizeSelector && $this->RecordCount <= $this->PageSize)) {
            $html .= $this->renderPageSizeSelector();
        }
        return $html;
    }

    // Render record info
    protected function renderRecordInfo(): string
    {
        $language = Language();
        $formatInteger = PROJECT_NAMESPACE . "FormatInteger";
        return <<<HTML
            <div class="ew-pager ew-rec">
                <div class="d-inline-flex">
                    <div class="ew-pager-rec me-1">{$language->phrase("Record")}</div>
                    <div class="ew-pager-start me-1">{$formatInteger($this->FromIndex)}</div>
                    <div class="ew-pager-to me-1">{$language->phrase("To")}</div>
                    <div class="ew-pager-end me-1">{$formatInteger($this->ToIndex)}</div>
                    <div class="ew-pager-of me-1">{$language->phrase("Of")}</div>
                    <div class="ew-pager-count me-1" data-count="{$this->RecordCount}">{$formatInteger($this->RecordCount)}</div>
                </div>
            </div>
        HTML;
    }

    // Render page size selector
    protected function renderPageSizeSelector(): string
    {
        $language = Language();
        $formatInteger = PROJECT_NAMESPACE . "FormatInteger";
        $pageSizes = explode(",", $this->PageSizes);
        $optionsHtml = '';
        foreach ($pageSizes as $pageSize) {
            $pageSize = trim($pageSize);
            $isAll = strtoupper($pageSize) === "ALL" || (int)$pageSize <= 0;
            if ($isAll) {
                $selected = $this->PageSizeAll ? ' selected' : '';
                $optionsHtml .= '<option value="ALL"' . $selected . '>' . $language->phrase("AllRecords") . '</option>';
            } else {
                $value = (int)$pageSize;
                $selected = $this->PageSize == $value ? ' selected' : '';
                $optionsHtml .= "<option value=\"{$value}\"{$selected}>" . $formatInteger($value) . "</option>";
            }
        }
        $tableRecPerPage = Config("TABLE_REC_PER_PAGE");
        $url = GetUrl($this->Url);
        $ajax = $this->UseAjaxActions ? "true" : "false";
        return <<<HTML
            <div class="ew-pager">
                <select name="{$tableRecPerPage}" class="form-select form-select-sm ew-tooltip"
                        title="{$language->phrase("RecordsPerPage")}" data-ew-action="change-page-size"
                        data-ajax="{$ajax}" data-url="{$url}">
                    {$optionsHtml}
                </select>
            </div>
        HTML;
    }
}
