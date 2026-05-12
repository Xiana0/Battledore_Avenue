<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Response;

/**
 * Export to JSON
 */
class ExportJson extends AbstractExport
{
    public string $FileExtension = "json";
    public string $Disposition = "inline";
    public bool $HasParent;
    protected mixed $Items;
    protected mixed $Item;

    // Style
    public function setStyle(string $style): void
    {
    }

    // Field caption
    public function exportCaption(DbField $fld): void
    {
    }

    // Field value
    public function exportValue(DbField $fld): void
    {
    }

    // Field aggregate
    public function exportAggregate(DbField $fld, string $type): void
    {
    }

    // Table header
    public function exportTableHeader(): void
    {
        $this->HasParent = isset($this->Items);
        if ($this->HasParent) {
            if (is_array($this->Items)) {
                $this->Items[$this->table->TableName] = [];
            } elseif (is_object($this->Items)) {
                $this->Items->{$this->table->TableName} = [];
            }
        }
    }

    // Export a value (caption, field value, or aggregate)
    protected function exportValueEx(DbField $fld, mixed $val): void
    {
    }

    // Begin a row
    public function beginExportRow(int $rowCnt = 0): void
    {
        if ($rowCnt <= 0) {
            return;
        }
        $this->Item = new \stdClass();
    }

    // End a row
    public function endExportRow(int $rowCnt = 0): void
    {
        if ($rowCnt <= 0) {
            return;
        }
        if ($this->HasParent) {
            if (is_array($this->Items)) {
                $this->Items[$this->table->TableName][] = $this->Item;
            } elseif (is_object($this->Items)) {
                $this->Items->{$this->table->TableName}[] = $this->Item;
            }
        } else {
            if (!isset($this->Items)) {
                $this->Items = $this->Item;
            } elseif (is_array($this->Items)) {
                $this->Items[] = $this->Item;
            } elseif (is_object($this->Items)) {
                $this->Items = [$this->Items, $this->Item]; // Convert to array
            } else {
                $this->Items = $this->Item;
            }
        }
    }

    // Empty row
    public function exportEmptyRow(): void
    {
    }

    // Page break
    public function exportPageBreak(): void
    {
    }

    // Export a field
    public function exportField(DbField $fld): void
    {
        if ($fld->Exportable && $fld->DataType != DataType::BLOB) {
            if ($fld->UploadMultiple) {
                $this->Item->{$fld->Name} = $fld->Upload->DbValue;
            } else {
                $this->Item->{$fld->Name} = $fld->exportValue();
            }
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
        $encodingOptions = IsDebug() ? JSON_PRETTY_PRINT : 0;
        $json = json_encode($this->Items, $encodingOptions);
        if ($json === false) {
            $json = json_encode(["json_encode_error" => json_last_error()], $encodingOptions);
        }
        $this->Text = $json;
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
