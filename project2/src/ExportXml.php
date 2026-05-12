<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export to XML
 */
class ExportXml extends AbstractExport
{
    public static string $NullString = "null";
    public bool $HasParent;
    public string $FileExtension = "xml";
    public string $Disposition = "inline";

    // Constructor
    public function __construct(
        protected RequestStack $requestStack,
        protected ?BaseDbTable $table = null,
        protected ?Response $response = null,
        protected XmlDocument $xmlDoc = new XmlDocument()
    ) { // Always utf-8
        parent::__construct($requestStack, $table, $response);
    }

    // Get XmlDocument
    public function getXmlDoc(): XmlDocument
    {
        return $this->xmlDoc;
    }

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
        $this->HasParent = is_object($this->xmlDoc->documentElement());
        if (!$this->HasParent) {
            $this->xmlDoc->addRoot($this->table->TableVar);
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
        if ($this->HasParent) {
            $this->xmlDoc->addRow($this->table->TableVar);
        } else {
            $this->xmlDoc->addRow();
        }
    }

    // End a row
    public function endExportRow(int $rowCnt = 0): void
    {
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
                $exportValue = $fld->Upload->DbValue;
            } else {
                $exportValue = $fld->exportValue();
            }
            if ($exportValue === null) {
                $exportValue = self::$NullString;
            }
            $this->xmlDoc->addField($fld->Param, $exportValue);
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
        $this->Text = $this->xmlDoc->xml();
        if ($save) { // Save to folder
            WriteFile(ExportPath() . $this->getSaveFileName(), $this->Text);
        }
        if ($output) { // Output
            $this->requestStack->getCurrentRequest()->attributes->set('_disable_csp', true); // Disable CSP
            $this->writeHeaders($fileName);
            $this->write();
        }
        return $this->response;
    }
}
