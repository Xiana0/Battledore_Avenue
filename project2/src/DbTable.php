<?php

namespace PHPMaker2026\Project1;

use ParagonIE\CSPBuilder\CSPBuilder;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Table class
 */
class DbTable extends BaseDbTable
{
    public string $CurrentMode = "view"; // Current mode
    public string $UpdateConflict = ""; // Update conflict
    public string $EventName = ""; // Event name
    public bool $EventCancelled = false; // Event cancelled
    public string $CancelMessage = ""; // Cancel message
    public bool $AllowAddDeleteRow = false; // Allow add/delete row
    public bool $ValidateKey = true; // Validate key
    public bool $DetailAdd = false; // Allow detail add
    public bool $DetailEdit = false; // Allow detail edit
    public bool $DetailView = false; // Allow detail view
    public bool $ShowMultipleDetails = false; // Show multiple details
    public int $GridAddRowCount = 0;
    public array $CustomActions = []; // Custom action array
    public bool $UseColumnVisibility = false;

    // Constructor
    public function __construct(
        Language $language,
        AdvancedSecurity $security,
        CSPBuilder $cspBuilder,
        CacheInterface $cache,
        FieldFactory $fieldFactory,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($language, $security, $cspBuilder, $cache, $fieldFactory, $dispatcher);
    }

    /**
     * Check current action
     */

    // Display
    public function isShow(): bool
    {
        return $this->CurrentAction == "show";
    }

    // Add
    public function isAdd(): bool
    {
        return in_array($this->CurrentAction, ["add", "inlineadd"]) && $this->security->canAdd();
    }

    // Copy
    public function isCopy(): bool
    {
        return in_array($this->CurrentAction, ["copy", "inlinecopy"]) && $this->security->canAdd();
    }

    // Edit
    public function isEdit(): bool
    {
        return in_array($this->CurrentAction, ["edit", "inlineedit"]) && $this->security->canEdit();
    }

    // Delete
    public function isDelete(): bool
    {
        return $this->CurrentAction == "delete";
    }

    // Confirm
    public function isConfirm(): bool
    {
        return $this->CurrentAction == "confirm";
    }

    // Overwrite
    public function isOverwrite(): bool
    {
        return $this->CurrentAction == "overwrite";
    }

    // Cancel
    public function isCancel(): bool
    {
        return $this->CurrentAction == "cancel";
    }

    // Grid add
    public function isGridAdd(): bool
    {
        return $this->CurrentAction == "gridadd" && $this->security->canAdd();
    }

    // Grid edit
    public function isGridEdit(): bool
    {
        return $this->CurrentAction == "gridedit" && $this->security->canEdit();
    }

    // Multi edit
    public function isMultiEdit(): bool
    {
        return $this->CurrentAction == "multiedit" && $this->security->canEdit();
    }

    // Add/Copy/Edit/GridAdd/GridEdit/MultiEdit
    public function isAddOrEdit(): bool
    {
        return $this->isAdd() || $this->isCopy() || $this->isEdit() || $this->isGridAdd() || $this->isGridEdit() || $this->isMultiEdit();
    }

    // Insert
    public function isInsert(): bool
    {
        return in_array($this->CurrentAction, ["insert", "inlineinsert"]) && $this->security->canAdd();
    }

    // Update
    public function isUpdate(): bool
    {
        return in_array($this->CurrentAction, ["update", "inlineupdate"]) && $this->security->canEdit();
    }

    // Grid action (Grid insert/update/multiupdate/overwrite)
    public function isGridAction(): bool
    {
        return $this->isGridUpdate() || $this->isGridInsert() || $this->isMultiUpdate() || $this->isGridOverwrite();
    }

    // Grid update
    public function isGridUpdate(): bool
    {
        return $this->CurrentAction == "gridupdate" && $this->security->canEdit();
    }

    // Grid insert
    public function isGridInsert(): bool
    {
        return $this->CurrentAction == "gridinsert" && $this->security->canAdd();
    }

    // Multi update
    public function isMultiUpdate(): bool
    {
        return $this->CurrentAction == "multiupdate" && $this->security->canEdit();
    }

    // Grid overwrite
    public function isGridOverwrite(): bool
    {
        return $this->CurrentAction == "gridoverwrite" && $this->security->canEdit();
    }

    // Import
    public function isImport(): bool
    {
        return $this->CurrentAction == "import" && $this->security->canImport();
    }

    // Search
    public function isSearch(): bool
    {
        return $this->CurrentAction == "search";
    }

    /**
     * Check last action
     */

    // Cancelled
    public function isCanceled(): bool
    {
        return $this->LastAction == "cancel" && !$this->CurrentAction;
    }

    // Inline inserted
    public function isInlineInserted(): bool
    {
        return in_array($this->LastAction, ["insert", "inlineinsert"]) && !$this->CurrentAction;
    }

    // Inline updated
    public function isInlineUpdated(): bool
    {
        return in_array($this->LastAction, ["update", "inlineupdate"]) && !$this->CurrentAction;
    }

    // Inline edit cancelled
    public function isInlineEditCancelled(): bool
    {
        return in_array($this->LastAction, ["edit", "inlineedit"]) && !$this->CurrentAction;
    }

    // Grid updated
    public function isGridUpdated(): bool
    {
        return $this->LastAction == "gridupdate" && !$this->CurrentAction;
    }

    // Grid inserted
    public function isGridInserted(): bool
    {
        return $this->LastAction == "gridinsert" && !$this->CurrentAction;
    }

    // Multi updated
    public function isMultiUpdated(): bool
    {
        return $this->LastAction == "multiupdate" && !$this->CurrentAction;
    }

    /**
     * Inline Add/Copy/Edit row
     */

    // Inline-Add row
    public function isInlineAddRow(): bool
    {
        return $this->isAdd() && $this->RowType == RowType::ADD;
    }

    // Inline-Copy row
    public function isInlineCopyRow(): bool
    {
        return $this->isCopy() && $this->RowType == RowType::ADD;
    }

    // Inline-Edit row
    public function isInlineEditRow(): bool
    {
        return $this->isEdit() && $this->RowType == RowType::EDIT;
    }

    // Inline-Add/Copy/Edit row
    public function isInlineActionRow(): bool
    {
        return $this->isInlineAddRow() || $this->isInlineCopyRow() || $this->isInlineEditRow();
    }

    /**
     * Other methods
     */

    // Records per page
    public function getRecordsPerPage(): int
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_REC_PER_PAGE"))) ?? 0;
    }

    public function setRecordsPerPage(int $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_REC_PER_PAGE")), $v);
    }

    // Start record number
    public function getStartRecordNumber(): int
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_START_REC"))) ?? 0;
    }

    public function setStartRecordNumber(int $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_START_REC")), $v);
    }

    // Search highlight name
    public function highlightName(): string
    {
        return $this->TableVar . "-highlight";
    }

    // Search highlight value
    public function highlightValue(DbField $fld): string
    {
        $kwlist = $this->BasicSearch->keywordList();
        if ($this->BasicSearch->Type == "") { // Auto, remove ALL "OR"
            $kwlist = array_diff($kwlist, ["OR"]);
        }
        $oprs = ["=", "LIKE", "STARTS WITH", "ENDS WITH"]; // Valid operators for highlight
        if (in_array($fld->AdvancedSearch->getSessionValue("z") ?? "", $oprs)) {
            $akw = $fld->AdvancedSearch->getSessionValue("x");
            if ($akw && strlen($akw) > 0) {
                $kwlist[] = $akw;
            }
        }
        if (in_array($fld->AdvancedSearch->getSessionValue("w") ?? "", $oprs)) {
            $akw = $fld->AdvancedSearch->getSessionValue("y");
            if ($akw && strlen($akw) > 0) {
                $kwlist[] = $akw;
            }
        }
        $src = $fld->getViewValue();
        if (count($kwlist) == 0) {
            return $src;
        }
        $pos1 = 0;
        $val = "";
        if (preg_match_all('/<([^>]*)>/i', $src ?: "", $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($matches as $match) {
                $pos2 = $match[0][1];
                if ($pos2 > $pos1) {
                    $src1 = substr($src, $pos1, $pos2 - $pos1);
                    $val .= $this->highlight($kwlist, $src1);
                }
                $val .= $match[0][0];
                $pos1 = $pos2 + strlen($match[0][0]);
            }
        }
        $pos2 = strlen($src ?: "");
        if ($pos2 > $pos1) {
            $src1 = substr($src, $pos1, $pos2 - $pos1);
            $val .= $this->highlight($kwlist, $src1);
        }
        return $val;
    }

    // Highlight keyword
    protected function highlight(array $kwlist, string $src): string
    {
        $pattern = '';
        foreach ($kwlist as $kw) {
            $pattern .= ($pattern == '' ? '' : '|') . preg_quote($kw, '/');
        }
        if ($pattern == '') {
            return $src;
        }
        $pattern = '/(' . $pattern . ')/u' . (Config("HIGHLIGHT_COMPARE") ? 'i' : '');
        $src = preg_replace_callback(
            $pattern,
            fn($match) => '<mark class="' . $this->highlightName() . ' mark ew-mark">' . $match[0] . '</mark>',
            $src
        );
        return $src;
    }

    // Search WHERE clause
    public function getSearchWhere(): string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_SEARCH_WHERE"))) ?? "";
    }

    public function setSearchWhere(string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_SEARCH_WHERE")), $v);
    }

    // Session WHERE clause
    public function getSessionWhere(): string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_WHERE"))) ?? "";
    }

    public function setSessionWhere(string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_WHERE")), $v);
    }

    // Session ORDER BY
    public function getSessionOrderBy(): string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_ORDER_BY"))) ?? "";
    }

    public function setSessionOrderBy(string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("TABLE_ORDER_BY")), $v);
    }

    // Session layout
    public function getSessionLayout(): ?string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("PAGE_LAYOUT")));
    }

    public function setSessionLayout(?string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_" . Config("PAGE_LAYOUT")), $v);
    }
}
