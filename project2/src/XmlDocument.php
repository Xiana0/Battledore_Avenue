<?php

namespace PHPMaker2026\Project1;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * XML document class
 */
class XmlDocument
{
    public string $RootTagName = '';
    public string $SubTblName = '';
    public string $RowTagName = '';
    public DOMElement|bool $XmlTbl = false;
    public DOMElement|bool $XmlSubTbl = false;
    public DOMElement|bool $XmlRow = false;

    // Constructor
    public function __construct(public DOMDocument $XmlDoc = new DOMDocument('1.0', 'utf-8'))
    {
    }

    // XML tag name
    protected function xmlTagName(string $name): string
    {
        if (!preg_match('/\A(?!XML)[a-z][\w0-9-]*/i', $name)) {
            $name = '_' . $name;
        }
        return $name;
    }

    // Load
    public function load(string $fileName): bool
    {
        $filePath = realpath($fileName);
        return file_exists($filePath) ? $this->XmlDoc->load($filePath) : false;
    }

    // Get document element
    public function documentElement(): ?DOMElement
    {
        return $this->XmlDoc->documentElement;
    }

    // Get attribute
    public function getAttribute(DOMElement $element, string $name): string
    {
        return $element ? $element->getAttribute($name) : '';
    }

    // Set attribute
    public function setAttribute(DOMElement $element, string $name, string $value): void
    {
        !$element || $element->setAttribute($name, $value);
    }

    // Select single node
    public function selectSingleNode(string $query): mixed
    {
        $elements = $this->selectNodes($query);
        return ($elements->length > 0) ? $elements->item(0) : null;
    }

    // Select nodes
    public function selectNodes(string $query): mixed
    {
        $xpath = new DOMXPath($this->XmlDoc);
        return $xpath->query($query);
    }

    // Add root
    public function addRoot(string $rootTagName = 'table'): void
    {
        $this->RootTagName = $this->xmlTagName($rootTagName);
        $this->XmlTbl = $this->XmlDoc->createElement($this->RootTagName);
        $this->XmlDoc->appendChild($this->XmlTbl);
    }

    // Add row
    public function addRow(string $tableTagName = '', string $rowTagName = 'row'): void
    {
        $this->RowTagName = $this->xmlTagName($rowTagName);
        $this->XmlRow = $this->XmlDoc->createElement($this->RowTagName);
        if ($tableTagName == '') {
            if ($this->XmlTbl) {
                $this->XmlTbl->appendChild($this->XmlRow);
            }
        } else {
            if ($this->SubTblName == '' || $this->SubTblName != $tableTagName) {
                $this->SubTblName = $this->xmlTagName($tableTagName);
                $this->XmlSubTbl = $this->XmlDoc->createElement($this->SubTblName);
                $this->XmlTbl->appendChild($this->XmlSubTbl);
            }
            if ($this->XmlSubTbl) {
                $this->XmlSubTbl->appendChild($this->XmlRow);
            }
        }
    }

    // Add field
    public function addField(string $name, string $value): void
    {
        $value ??= 'null';
        $xmlfld = $this->XmlDoc->createElement($this->xmlTagName($name));
        $this->XmlRow->appendChild($xmlfld);
        $xmlfld->appendChild($this->XmlDoc->createTextNode($value));
    }

    // Get XML
    public function xml(): string|false
    {
        return $this->XmlDoc->saveXML();
    }
}
