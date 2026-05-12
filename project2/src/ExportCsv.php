<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Response;

/**
 * Export to CSV
 */
class ExportCsv extends AbstractExport
{
    public string $FileExtension = "csv";
    public bool $UseCharset = true; // Add charset to content type
    public bool $UseBom = true; // Output byte order mark
    public string $QuoteChar = "\"";
    public string $Separator = ",";
    public string $LineBreak = "\r\n";

    // Style
    public function setStyle(string $style): void
    {
        $this->Horizontal = true;
    }

    // Set horizontal
    public function setHorizontal(bool $value): void
    {
        $this->Horizontal = true;
    }

    // Table header
    public function exportTableHeader(): void
    {
    }

    // Export a value (caption, field value, or aggregate)
    protected function exportValueEx(DbField $fld, mixed $val): void
    {
        if ($fld->DataType != DataType::BLOB) {
            if ($this->Line != "") {
                $this->Line .= $this->Separator;
            }
            $this->Line .= $this->QuoteChar . str_replace($this->QuoteChar, $this->QuoteChar . $this->QuoteChar, strval($val)) . $this->QuoteChar;
        }
    }

    // Field aggregate
    public function exportAggregate(DbField $fld, string $type): void
    {
    }

    // Begin a row
    public function beginExportRow(int $rowCnt = 0): void
    {
        $this->Line = "";
    }

    // End a row
    public function endExportRow(int $rowCnt = 0): void
    {
        $this->Line .= $this->LineBreak;
        $this->Text .= $this->Line;
    }

    // Empty row
    public function exportEmptyRow(): void
    {
    }

    // Export a field
    public function exportField(DbField $fld): void
    {
        if (!$fld->Exportable) {
            return;
        }
        if ($fld->UploadMultiple) {
            $this->exportValueEx($fld, $fld->Upload->DbValue);
        } else {
            $this->exportValue($fld);
        }
    }

    // Table Footer
    public function exportTableFooter(): void
    {
    }

    // Add HTML tags
    public function exportHeaderAndFooter(): void
    {
    }

    // Export
    public function export(string $fileName = "", bool $output = true, bool $save = false): Response
    {
        if ($save) { // Save to folder
            WriteFile(ExportPath() . $this->getSaveFileName(), $this->Text);
        }
        if ($output) { // Output
            $this->writeHeaders($fileName);
            $this->write();
        }
        return $this->response;
    }
}
