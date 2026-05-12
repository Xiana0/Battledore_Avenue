<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Basic Search class
 */
class BasicSearch
{
    public bool $BasicSearchAnyFields;
    public string $Keyword = "";
    public string $KeywordDefault = "";
    public string $Type = "";
    public string $TypeDefault = "";
    public bool $Raw = false;
    protected string $Prefix = "";

    // Constructor
    public function __construct(
        public BaseDbTable $Table,
        protected SessionInterface $session,
        protected Language $language
    ) {
        $this->BasicSearchAnyFields = Config("BASIC_SEARCH_ANY_FIELDS");
        $this->Prefix = PROJECT_NAME . "_" . $this->Table->TableVar . "_";
        $this->Raw = !Config("REMOVE_XSS");
    }

    // Session variable name
    protected function getSessionName(string $suffix): string
    {
        return AddTabId($this->Prefix . $suffix);
    }

    // Load default
    public function loadDefault(): void
    {
        $this->Keyword = $this->KeywordDefault;
        $this->Type = $this->TypeDefault;
        if (!$this->session->has($this->getSessionName(Config("TABLE_BASIC_SEARCH_TYPE"))) && $this->TypeDefault != "") { // Save default to session
            $this->setType($this->TypeDefault);
        }
    }

    // Unset session
    public function unsetSession(): void
    {
        $this->session->remove($this->getSessionName(Config("TABLE_BASIC_SEARCH_TYPE")));
        $this->session->remove($this->getSessionName(Config("TABLE_BASIC_SEARCH")));
    }

    // Isset session
    public function issetSession(): bool
    {
        return $this->session->has($this->getSessionName(Config("TABLE_BASIC_SEARCH")));
    }

    // Set keyword
    public function setKeyword(string $v, bool $save = true): void
    {
        $v = $this->Raw ? $v : RemoveXss($v);
        $this->Keyword = $v;
        if ($save) {
            $this->session->set($this->getSessionName(Config("TABLE_BASIC_SEARCH")), $v);
        }
    }

    // Set type
    public function setType(string $v, bool $save = true): void
    {
        $this->Type = $v;
        if ($save) {
            $this->session->set($this->getSessionName(Config("TABLE_BASIC_SEARCH_TYPE")), $v);
        }
    }

    // Save
    public function save(): void
    {
        $this->session->set($this->getSessionName(Config("TABLE_BASIC_SEARCH")), $this->Keyword);
        $this->session->set($this->getSessionName(Config("TABLE_BASIC_SEARCH_TYPE")), $this->Type);
    }

    // Get keyword
    public function getKeyword(): string
    {
        return $this->session->get($this->getSessionName(Config("TABLE_BASIC_SEARCH"))) ?? "";
    }

    // Get type
    public function getType(): string
    {
        return $this->session->get($this->getSessionName(Config("TABLE_BASIC_SEARCH_TYPE"))) ?? "";
    }

    // Get type name
    public function getTypeName(): string
    {
        $typ = $this->getType();
        return match ($typ) {
            "=" => $this->language->phrase("QuickSearchExact"),
            "AND" => $this->language->phrase("QuickSearchAll"),
            "OR" => $this->language->phrase("QuickSearchAny"),
            default => $this->language->phrase("QuickSearchAuto")
        };
    }

    // Get short type name
    public function getTypeNameShort(): string
    {
        $typ = $this->getType();
        $typname = match ($typ) {
            "=" => $this->language->phrase("QuickSearchExactShort"),
            "AND" => $this->language->phrase("QuickSearchAllShort"),
            "OR" => $this->language->phrase("QuickSearchAnyShort"),
            default => $this->language->phrase("QuickSearchAutoShort")
        };
        if ($typname != "") {
            $typname .= "&nbsp;";
        }
        return $typname;
    }

    // Get keyword list
    public function keywordList(bool $default = false): array
    {
        $searchKeyword = $default ? $this->KeywordDefault : $this->Keyword;
        $searchType = $default ? $this->TypeDefault : $this->Type;
        if ($searchKeyword != "") {
            $search = trim($searchKeyword);
            $ar = GetQuickSearchKeywords($search, $searchType);
            return $ar;
        }
        return [];
    }

    // Load
    public function load(): void
    {
        $this->Keyword = $this->getKeyword();
        $this->Type = $this->getType();
    }
}
