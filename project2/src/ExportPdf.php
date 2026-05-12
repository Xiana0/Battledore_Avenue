<?php

namespace PHPMaker2026\Project1;

use Dompdf\Dompdf;
use DiDom\Document;
use DiDom\Element;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class for export to PDF
 */
class ExportPdf extends AbstractExport
{
    public static array $Options = [];
    public string $PdfBackend = "";
    public string $FileExtension = "pdf";
    public string $PageSize = "a4";
    public string $PageOrientation = "portrait";

    // Constructor
    public function __construct(
        protected RequestStack $requestStack,
        protected ?BaseDbTable $table = null,
        protected ?Response $response = null,
        public Dompdf $dompdf = new Dompdf()
    ) {
        parent::__construct($requestStack, $table, $response);
        $this->PdfBackend = Config("PDF_BACKEND");
        $this->StyleSheet = Config("PDF_STYLESHEET_FILENAME");
        $this->setTable($table);
    }

    // Get Dompdf
    public function getDompdf(): Dompdf
    {
        return $this->dompdf;
    }

    /**
     * Set table
     *
     * @param BaseDbTable $value Table/Page object
     * @return static
     */
    #[\Override]
    public function setTable(?BaseDbTable $value): static
    {
        parent::setTable($value);
        if ($this->table) {
            $this->PageSize = $this->table->ExportPageSize;
            $this->PageOrientation = $this->table->ExportPageOrientation;
        }
        return $this;
    }

    // Table header
    public function exportTableHeader(): void
    {
        $this->Text .= "<table class=\"ew-table\">\r\n";
    }

    // Export a value (caption, field value, or aggregate)
    protected function exportValueEx(DbField $fld, mixed $val): void
    {
        $wrkVal = strval($val);
        $wrkVal = "<td" . ($this->ExportStyles ? $fld->cellStyles() : "") . ">" . $wrkVal . "</td>\r\n";
        $this->Line .= $wrkVal;
        $this->Text .= $wrkVal;
    }

    // Begin a row
    public function beginExportRow(int $rowCnt = 0): void
    {
        $this->FldCnt = 0;
        if ($this->Horizontal) {
            if ($rowCnt == -1) {
                $classname = "ew-table-footer";
            } elseif ($rowCnt == 0) {
                $classname = "ew-table-header";
            } else {
                $classname = (($rowCnt % 2) == 1) ? "" : "ew-table-alt-row";
            }
            $this->Line = "<tr" . ($this->ExportStyles ? ' class="' . $classname . '"' : '') . ">";
            $this->Text .= $this->Line;
        }
    }

    // End a row
    public function endExportRow(int $rowCnt = 0): void
    {
        if ($this->Horizontal) {
            $this->Line .= "</tr>";
            $this->Text .= "</tr>";
            if ($rowCnt == 0) {
                $this->Header = $this->Line;
            }
        }
    }

    // Page break
    public function exportPageBreak(): void
    {
        if ($this->Horizontal) {
            $this->Text .= "</table>\r\n" . // End current table
                Config("PAGE_BREAK_HTML") . "\r\n" . // Page break
                "<table class=\"ew-table\">\r\n" . // New table
                $this->Header; // Add table header
        }
    }

    // Export a field
    public function exportField(DbField $fld): void
    {
        if (!$fld->Exportable) {
            return;
        }
        $exportValue = $fld->exportValue();
        if ($fld->ExportFieldImage && $fld->ViewTag == "IMAGE") {
            $exportValue = GetFileImgTag($fld->getTempImage());
        } elseif ($fld->ExportFieldImage && $fld->ExportHrefValue != "") { // Export custom view tag
            $exportValue = GetFileImgTag($fld->ExportHrefValue);
        } else {
            $exportValue = str_replace("<br>", "\r\n", $exportValue ?? "");
            $exportValue = strip_tags($exportValue);
            $exportValue = str_replace("\r\n", "<br>", $exportValue ?? "");
        }
        if ($this->Horizontal) { // Horizontal
            $this->exportValueEx($fld, $exportValue);
        } else { // Vertical, export as a row
            $this->FldCnt++;
            $fld->CellCssClass = ($this->FldCnt % 2 == 1) ? "" : "ew-table-alt-row";
            $cellStyles = $this->ExportStyles ? $fld->cellStyles() : "";
            $this->Text .= "<tr><td" . $cellStyles . ">" . $fld->exportCaption() . "</td>" .
                "<td" . $cellStyles . ">" . $exportValue . "</td></tr>";
        }
    }

    /**
     * Append image
     *
     * @param string $imagefn Image file
     * @param ?string $break Break type (before/after/none)
     * @return void
     */
    #[\Override]
    public function addImage(string $imagefn, ?string $break = null): void
    {
        $classes = "ew-export";
        if (SameText($break, "before")) {
            $classes .= " break-before-page";
        } elseif (SameText($break, "after")) {
            $classes .= " break-after-page";
        } elseif (SameText($break, "none")) {
            $classes .= " break-after-avoid";
        }
        if (FileExists($imagefn)) { // Use temp image for TCPDF
            $imagefn = TempImage(ReadFile($imagefn));
        }
        $html = '<div class="' . $classes . '">' . GetFileImgTag($imagefn) . "</div>";
        if (ContainsText($this->Text, "</body>")) {
            $this->Text = str_replace("</body>", $html . "</body>", $this->Text); // Insert before </body>
        } else {
            $this->Text .= $html; // Append to end
        }
    }

    // Adjust HTML before export
    protected function adjustHtml(): void
    {
        if (!ContainsText($this->Text, "</body>")) {
            $this->exportHeaderAndFooter(); // Add header and footer to $this->Text
        }
        $doc = $this->getDocument($this->Text);
        $this->adjustPageBreak($doc);
        $css = $this->styles();
        $style = $doc->first("head > style");
        if (!$style) {
            $style = $doc->createElement("style", $css);
            $head = $doc->first("head");
            if (!$head) {
                $head = $doc->createElement("head");
                $doc->appendChild($head);
            }
            $head->appendChild($style); // Add style tag
        } elseif ($style && $style->text() != $css) {
            $style->setValue($css); // Replace styles for PDF
        }
        $spans = $doc->find("span");
        foreach ($spans as $span) {
            $classNames = $span->getAttribute("class") ?? "";
            if ($classNames == "ew-filter-caption") { // Insert colon
                $span->parent()->insertBefore($doc->createElement("span", ":&nbsp;"), $span->nextSibling());
            } elseif (preg_match('/\bicon\-\w+\b/', $classNames)) { // Remove icons
                $span->remove();
            }
        }

        // Remove card headers
        $divs = $doc->find("div.card-header");
        array_walk($divs, fn($el) => $el->remove());

        // Set image sizes
        $images = $doc->find("img");
        $portrait = SameText($this->PageOrientation, "portrait");
        foreach ($images as $image) {
            $imagefn = $image->getAttribute("src") ?? "";
            if (FileExists($imagefn)) {
                $size = getimagesize(PrefixPath($imagefn)); // Get image size
                if ($size[0] != 0) {
                    if (SameText($this->PageSize, "letter")) { // Letter paper (8.5 in. by 11 in.)
                        $w = $portrait ? 216 : 279;
                    } elseif (SameText($this->PageSize, "legal")) { // Legal paper (8.5 in. by 14 in.)
                        $w = $portrait ? 216 : 356;
                    } else {
                        $w = $portrait ? 210 : 297; // A4 paper (210 mm by 297 mm)
                    }
                    $w = min($size[0], ($w - 20 * 2) / 25.4 * 72 * Config("PDF_IMAGE_SCALE_FACTOR")); // Resize image, adjust the scale factor if necessary
                    $h = $w / $size[0] * $size[1];
                    $image->setAttribute("width", $w);
                    $image->setAttribute("height", $h);
                }
            }
        }

        // Output HTML
        $this->setDocument($doc);
    }

    // Export
    public function export(string $fileName = "", bool $output = true, bool $save = false): Response
    {
        @set_time_limit(Config("PDF_TIME_LIMIT"));
        $this->adjustHtml();
        $options = new \Dompdf\Options(self::$Options);
        $options->set("pdfBackend", $this->PdfBackend);
        $options->set("isRemoteEnabled", true); // Support remote images such as S3
        $chroot = $options->getChroot();
        $chroot[] = PrefixDirectoryPath(UploadTempPathRoot());
        $chroot[] = PrefixDirectoryPath(UploadTempPath());
        $chroot[] = dirname(CssFile(Config("PDF_STYLESHEET_FILENAME"), false));
        $options->setChroot($chroot);
        $this->dompdf->setOptions($options);
        $this->dompdf->loadHtml($this->Text);
        $this->dompdf->setPaper($this->PageSize, $this->PageOrientation);
        $this->dompdf->render();
        $this->Text = $this->dompdf->output();
        if ($save) { // Save to folder
            WriteFile(ExportPath() . $this->getSaveFileName(), $this->Text);
        }
        if ($output) { // Output
            $this->writeHeaders($fileName);
            $this->write();
        }
        return $this->response;
    }

    // Destructor
    public function __destruct()
    {
    }
}
