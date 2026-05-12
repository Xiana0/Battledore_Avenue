<?php

namespace PHPMaker2026\Project1;

use DiDom\Document;
use DiDom\Element;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export to Word
 */
class ExportWord extends AbstractExport
{
    public string $FileExtension = "doc";
    public bool $UseCharset = true; // Add charset to content type
    public bool $UseBom = true; // Output byte order mark
    public bool $UseInlineStyles = true; // Use inline styles (Does not support multiple CSS classes)
    public bool $ExportImages = false; // Does not support images

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
