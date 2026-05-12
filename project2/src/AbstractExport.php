<?php

namespace PHPMaker2026\Project1;

use DiDom\Document;
use DiDom\Element;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract class for export
 */
abstract class AbstractExport extends BaseAbstractExport
{
    public static string $Selectors = "table.ew-table, table.ew-export-table, div.ew-chart, *.ew-export"; // Elements to be exported
    public static string $ExcludedSelectors = ""; // Elements to be excluded
    public string $Line = "";
    public string $Header = "";
    public string $Style = "h"; // "v"(Vertical) or "h"(Horizontal)
    public bool $Horizontal = true; // Horizontal
    public bool $ExportCustom = false;
    public string $StyleSheet = ""; // Style sheet path (relative to project folder)
    public bool $UseInlineStyles = false; // Use inline styles for page breaks
    public bool $ExportImages = true; // Allow exporting images
    public bool $ExportPageBreaks = true; // Page breaks when export
    public bool $ExportStyles = true; // CSS styles when export
    protected int $RowCnt = 0;
    protected int $FldCnt = 0;

    // Constructor
    public function __construct(
        protected RequestStack $requestStack,
        protected ?BaseDbTable $table = null,
        protected ?Response $response = null,
    ) {
        parent::__construct($requestStack, $table, $response);
        $this->StyleSheet = Config("PROJECT_STYLESHEET_FILENAME");
        $this->ExportStyles = Config("EXPORT_CSS_STYLES");
    }

    // Style
    public function setStyle(string $style): void
    {
        $style = strtolower($style);
        if (in_array($style, ["v", "h"])) {
            $this->Style = $style;
        }
        $this->Horizontal = $this->Style != "v";
    }

    // Set horizontal
    public function setHorizontal(bool $value): void
    {
        $this->Horizontal = $value;
        $this->Style = $this->Horizontal ? "h" : "v";
    }

    // Field caption
    public function exportCaption(DbField $fld): void
    {
        if (!$fld->Exportable) {
            return;
        }
        $this->FldCnt++;
        $this->exportValueEx($fld, $fld->exportCaption());
    }

    // Field value
    public function exportValue(DbField $fld): void
    {
        $this->exportValueEx($fld, $fld->exportValue());
    }

    // Field aggregate
    public function exportAggregate(DbField $fld, string $type): void
    {
        if (!$fld->Exportable) {
            return;
        }
        $this->FldCnt++;
        if ($this->Horizontal) {
            $val = "";
            if (in_array($type, ["TOTAL", "COUNT", "AVERAGE"])) {
                $val = Language()->phrase($type) . ": " . $fld->exportValue();
            }
            $this->exportValueEx($fld, $val);
        }
    }

    // Get meta tag for charset
    protected function charsetMetaTag(): string
    {
        return "<meta http-equiv=\"Content-Type\" content=\"text/html" . (PROJECT_CHARSET != "" ? "; charset=" . PROJECT_CHARSET : "") . "\">\r\n";
    }

    // Table header
    public function exportTableHeader(): void
    {
        $this->Text .= "<table class=\"ew-export-table\">";
    }

    // Cell styles
    protected function cellStyles(DbField $fld): string
    {
        return $this->ExportStyles ? $fld->cellStyles() : "";
    }

    // Export a value (caption, field value, or aggregate)
    protected function exportValueEx(DbField $fld, mixed $val): void
    {
        $this->Text .= "<td" . $this->cellStyles($fld) . ">" . strval($val) . "</td>";
    }

    // Begin a row
    public function beginExportRow(int $rowCnt = 0): void
    {
        $this->RowCnt++;
        $this->FldCnt = 0;
        if ($this->Horizontal) {
            if ($rowCnt == -1) {
                $classname = "ew-export-table-footer";
            } elseif ($rowCnt == 0) {
                $classname = "ew-export-table-header";
            } else {
                $classname = (($rowCnt % 2) == 1) ? "ew-export-table-row" : "ew-export-table-alt-row";
            }
            $this->Text .= "<tr" . ($this->ExportStyles ? ' class="' . $classname . '"' : '') . ">";
        }
    }

    // End a row
    public function endExportRow(int $rowCnt = 0): void
    {
        if ($this->Horizontal) {
            $this->Text .= "</tr>";
        }
    }

    // Empty row
    public function exportEmptyRow(): void
    {
        $this->RowCnt++;
        $this->Text .= "<br>";
    }

    // Page break
    public function exportPageBreak(): void
    {
    }

    // Export field value
    public function exportFieldValue(DbField $fld): mixed
    {
        $exportValue = "";
        if ($fld->ExportFieldImage && $fld->ExportHrefValue != "" && is_object($fld->Upload)) { // Upload field
            // Note: Cannot show image, show empty content
            // if (!IsEmpty($fld->Upload->DbValue)) {
            //    $exportValue = GetFileATag($fld, $fld->ExportHrefValue);
            // }
        } else {
            $exportValue = $fld->exportValue();
        }
        return $exportValue;
    }

    // Export a field
    public function exportField(DbField $fld): void
    {
        if (!$fld->Exportable) {
            return;
        }
        $this->FldCnt++;
        $exportValue = $this->exportFieldValue($fld);
        if ($this->Horizontal) {
            $this->exportValueEx($fld, $exportValue);
        } else { // Vertical, export as a row
            $this->RowCnt++;
            $this->Text .= "<tr class=\"" . (($this->FldCnt % 2 == 1) ? "ew-export-table-row" : "ew-export-table-alt-row") . "\">" .
                "<td" . $this->cellStyles($fld) . ">" . $fld->exportCaption() . "</td>" .
                "<td" . $this->cellStyles($fld) . ">" . $exportValue . "</td></tr>";
        }
    }

    // Table footer
    public function exportTableFooter(): void
    {
        $this->Text .= "</table>";
    }

    // Add HTML tags
    public function exportHeaderAndFooter(): void
    {
        $this->Text = "<html><head>" . $this->charsetMetaTag() .
            "<style" . Nonce() . ">" . $this->styles() . "</style></head>" .
            "<body>" . $this->Text . "</body></html>";
    }

    // Get CSS rules
    public function styles(): string
    {
        if ($this->ExportStyles && $this->StyleSheet != "") {
            $path = __DIR__ . "/../" . $this->StyleSheet;
            if (file_exists($path)) {
                return file_get_contents($path);
            }
        }
        return "";
    }

    // Adjust page break
    protected function adjustPageBreak(object $doc): void
    {
        // Remove empty charts
        $divs = $doc->find("div.ew-chart");
        foreach ($divs as $div) {
            $script = $div->nextSibling("script");
            !$script || $script->remove(); // Remove script for chart
            $div->has("img") || $div->remove(); // No image inside => Remove
        }
        // Remove empty cards
        $cards = $doc->find("div.card");
        array_walk($cards, fn($el) => $el->has(self::$Selectors) || $el->remove()); // Nothing to export => Remove
        // Find and process all elements to be exported
        $elements = $doc->first("body")->findInDocument(self::$Selectors);
        $break = $this->table ? $this->table->ExportPageBreaks : $this->ExportPageBreaks;
        $avoid = false;
        for ($i = 0, $cnt = count($elements); $i < $cnt; $i++) {
            $element = $elements[$i];
            $classes = $element->classes();
            $style = $element->style();
            if ($this->UseInlineStyles) { // Use inline styles
                $classes->remove("break-before-page")->remove("break-after-page"); // Remove classes
            } else { // Use classes
                $style->removeProperty("page-break-before")->removeProperty("page-break-after"); // Remove styles
            }
            if ($i == 0) { // First, remove page break before content
                if ($this->UseInlineStyles) {
                    $style->removeProperty("page-break-before")->removeProperty("page-break-after");
                } else {
                    $classes->remove("break-before-page")->remove("break-after-page");
                }
            } elseif ($i == $cnt - 1) { // Last, remove page break after content
                if ($this->UseInlineStyles) {
                    $break && !$avoid ? $style->setProperty("page-break-before", "always") : $style->removeProperty("page-break-before");
                    $style->removeProperty("page-break-after");
                } else {
                    $break && !$avoid ? $classes->add("break-before-page") : $classes->remove("break-before-page");
                    $classes->remove("break-after-page");
                }
            } else {
                $prev = $element->previousSibling();
                if ($prev?->isElementNode() && $prev->style()->getProperty("page-break-after") == "always") { // PAGE_BREAK_HTML
                    $avoid = true;
                }
                if ($this->UseInlineStyles) {
                    $break && !$avoid ? $style->setProperty("page-break-before", "always") : $style->removeProperty("page-break-before");
                    $style->removeProperty("page-break-after");
                } else {
                    $break && !$avoid ? $classes->add("break-before-page") : $classes->remove("break-before-page");
                    $classes->remove("break-after-page");
                }
            }
            $avoid = $classes->contains("break-after-avoid");
        }
    }

    // Get document
    public function getDocument(?string $string = null): object
    {
        $doc = new Document(null, false, PROJECT_ENCODING);
        $string ??= $this->Text;
        !$string || @$doc->load($string);
        return $doc;
    }

    // Set document
    public function setDocument(Document $doc): void
    {
        $this->Text = $doc->format()->html();
    }

    // Remove elements from document
    public function removeElements(Document $doc): void
    {
        if (self::$ExcludedSelectors) {
            $elements = $doc->first("body")->findInDocument(self::$ExcludedSelectors);
            array_walk($elements, fn($el) => $el->remove()); // Remove
        }
    }

    // Adjust HTML before export (to be called in export())
    protected function adjustHtml(): void
    {
        if (!ContainsText($this->Text, "</body>")) {
            $this->exportHeaderAndFooter(); // Add header and footer to $this->Text
        }
        $doc = $this->getDocument($this->Text); // Load $this->Text again
        // Remove excluded elements
        $this->removeElements($doc);
        // Images
        if (!$this->ExportImages) {
            $imgs = $doc->find("img");
            array_walk($imgs, fn($el) => $el->remove());
        }
        // Adjust page break
        $this->adjustPageBreak($doc);
        // Grid and table container
        $divs = $doc->find("div[class*='ew-grid'], div[class*='table-responsive']"); // div.ew-grid(-middle-panel), div.table-responsive(-sm|-md|-lg|-xl)
        foreach ($divs as $div) {
            $div->removeAttribute("class");
        }
        // Table
        $tables = $doc->find(".ew-table, .ew-export-table");
        foreach ($tables as $table) {
            $classes = $table->classes();
            $noBorder = $classes->contains("no-border");
            if ($classes->contains("ew-table")) {
                if ($this->UseInlineStyles) {
                    $classes->removeAll()->add("ew-export-table"); // Use single class (for MS Word/Excel)
                } else {
                    $classes->removeAll(["break-before-page", "break-after-page"])->add("ew-export-table");
                }
            }
            $table->style()->setProperty("border-collapse", "collapse"); // Set border-collapse
            $rows = $table->findInDocument("tr"); // Note: Use findInDocument() to change styles
            $cellStyles = Config("EXPORT_TABLE_CELL_STYLES");
            if ($noBorder) {
                $cellStyles["border"] = "0";
            }
            foreach ($rows as $row) {
                $cells = $row->findInDocument("td, th"); // Note: Use findInDocument() to change styles
                foreach ($cells as $cell) {
                    $cell->style()->setMultipleProperties($cellStyles); // Add cell styles
                }
            }
        }
        $this->setDocument($doc);
    }

    // Load HTML
    public function loadHtml(string $html): void
    {
        $this->Text .= $html;
    }

    // Add image (virtual)
    public function addImage(string $imagefn, ?string $break = null): void
    {
        // To be implemented by subclass
    }

    // Export
    abstract public function export(string $fileName = "", bool $output = true, bool $save = false): Response;
}
