<?php

namespace PHPMaker2026\Project1;

use DiDom\Document;
use DiDom\Element;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export to Excel
 */
class ExportExcel extends AbstractExport
{
    public string $FileExtension = "xls";
    public bool $UseCharset = true; // Add charset to content type
    public bool $UseBom = true; // Output byte order mark
    public bool $UseInlineStyles = true; // Use inline styles (Does not support multiple CSS classes)
    public bool $ExportImages = false; // Does not support images

    // Export a value (caption, field value, or aggregate)
    protected function exportValueEx(DbField $fld, mixed $val): void
    {
        if (in_array($fld->DataType, [DataType::STRING, DataType::MEMO]) && is_numeric($val)) {
            $val = "=\"" . strval($val) . "\"";
        }
        $this->Text .= parent::exportValueEx($fld, $val);
    }

    // Export
    public function export(string $fileName = "", bool $output = true, bool $save = false): Response
    {
        $this->adjustHtml();
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
