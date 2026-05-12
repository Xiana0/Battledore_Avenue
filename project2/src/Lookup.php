<?php

namespace PHPMaker2026\Project1;

use Symfony\Contracts\Cache\ItemInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Exception;

/**
 * Lookup class
 */
class Lookup
{
    protected ?object $renderer = null;
    protected bool $rendering = false;
    public static string $ModalLookupSearchType = "AND"; // "AND" or "OR" or "=" or ""
    public static string $ModalLookupSearchOperator = "LIKE"; // "LIKE" or "STARTS WITH" or "ENDS WITH"
    public static bool $KeepCrLf = false;
    public string $LookupType = "";
    public ?array $Options = null;
    public string $CurrentFilter = "";
    public string $UserSelect = "";
    public string $UserFilter = "";
    public array $FilterValues = [];
    public string $SearchValue = "";
    public int $PageSize = -1;
    public int $Offset = -1;
    public string $LookupFilter = "";
    public ?BaseDbTable $Table = null;
    public bool $FormatLookup = true; // Can be disabled by setting to false
    public ?bool $FormatAutoFill = null; // Can be disabled by setting to false
    public bool $UseParentFilter = false;
    public bool $LookupAllDisplayFields = false;
    public bool $UseTableFilterForFilterFields = false;

    /**
     * Constructor
     */
    public function __construct(
        public ?DbField $Field = null,
        public string $LinkTable = "",
        public bool $Distinct = false,
        public string $LinkField = "",
        public array $DisplayFields = [],
        public string $GroupByField = "",
        public string $GroupByExpression = "",
        public array $ParentFields = [],
        public array $ChildFields = [],
        public array $FilterFields = [],
        public array $FilterFieldVars = [],
        public array $AutoFillSourceFields = [],
        public array $AutoFillTargetFields = [],
        public bool $IsAutoFillTargetField = false,
        public string $UserOrderBy = "",
        public string $Template = "",
        public string $SearchExpression = ""
    ) {
        if (array_is_list($this->FilterFields)) {
            $this->FilterFields = array_fill_keys($this->FilterFields, "="); // Default filter operator
        }
        $this->LookupAllDisplayFields = Config("LOOKUP_ALL_DISPLAY_FIELDS");
        $this->UseTableFilterForFilterFields = Config("USE_TABLE_FILTER_FOR_FILTER_FIELDS");
    }

    /**
     * Get lookup SQL (as QueryBuilder) based on current filter/lookup filter, call Lookup_Selecting if necessary
     *
     * @param bool $useParentFilter
     * @param string $currentFilter
     * @param string|callable $lookupFilter
     * @param PageInterface|BaseDbTable|null $page
     * @param bool $skipFilterFields
     * @param bool $clearUserFilter
     * @return ?QueryBuilder
     */
    public function getSqlBuilder(
        bool $useParentFilter = true,
        string $currentFilter = "",
        string|callable $lookupFilter = "",
        PageInterface|BaseDbTable|null $page = null,
        bool $skipFilterFields = false,
        bool $clearUserFilter = false): ?QueryBuilder
    {
        $this->UseParentFilter = $useParentFilter; // Save last call
        $this->CurrentFilter = $currentFilter;
        $this->LookupFilter = $lookupFilter; // Save last call
        if ($clearUserFilter) {
            $this->UserFilter = "";
        }
        $filter = $this->getWhere($useParentFilter);
        $newFilter = $filter;
        $fld = $page?->Fields[$this->Field->Name] ?? null;
        if ($fld != null && $page instanceof LookupTableInterface) {
            $page->lookupSelecting($fld, $newFilter); // Call Lookup Selecting
        }
        if ($filter != $newFilter) { // Filter changed
            AddFilter($this->UserFilter, $newFilter);
        }
        if ($lookupFilter != "") { // Add lookup filter as part of user filter
            AddFilter($this->UserFilter, $lookupFilter);
        }
        $sql = $this->getSqlPart("", true, $useParentFilter, $skipFilterFields); // Return string|QueryBuilder|null
        return $sql instanceof QueryBuilder ? $sql : null;
    }

    /**
     * Get lookup SQL (as string) based on current filter/lookup filter, call Lookup_Selecting if necessary
     *
     * @param bool $useParentFilter
     * @param string $currentFilter
     * @param string|callable $lookupFilter
     * @param PageInterface|BaseDbTable|null $page
     * @param bool $skipFilterFields
     * @param bool $clearUserFilter
     * @return string
     */
    public function getSql(
        bool $useParentFilter = true,
        string $currentFilter = "",
        string|callable $lookupFilter = "",
        PageInterface|BaseDbTable|null $page = null,
        bool $skipFilterFields = false,
        bool $clearUserFilter = false): string
    {
        return $this->getSqlBuilder($useParentFilter, $currentFilter, $lookupFilter, $page, $skipFilterFields, $clearUserFilter)?->getSQL() ?? "";
    }

    /**
     * Set options
     *
     * @param array $options Input options with formats:
     *  1. Manual input data, e.g.: [ ["lv1", "dv", "dv2", "dv3", "dv4"], ["lv2", "dv", "dv2", "dv3", "dv4"], ...]
     *  2. Data from fetchAllAssociative(), e.g.: [ ["Field1" => "lv1", "Field2" => "dv2", ...], ["Field1" => "lv2", "Field2" => "dv2", ...], ...]
     * @return bool Output array ["lv1" => ["lf" => "lv1", "df" => "dv", ...], ...]
     */
    public function setOptions(array $options): bool
    {
        $opts = $this->formatOptions($options);
        if ($opts === null) {
            return false;
        }
        $this->Options = $opts;
        return true;
    }

    /**
     * Set filter field operator
     *
     * @param string $name Filter field name
     * @param string $opr Filter search operator
     * @return void
     */
    public function setFilterOperator(string $name, string $opr): void
    {
        if (array_key_exists($name, $this->FilterFields) && IsValidOperator($opr)) {
            $this->FilterFields[$name] = $opr;
        }
    }

    /**
     * Get user parameters hidden tag, if user SELECT/WHERE/ORDER BY clause is not empty
     *
     * @param ?PageInterface $page Page
     * @param string $var Variable name
     * @return string
     */
    public function getParamTag(?PageInterface $page, string $var): string
    {
        $this->UserSelect = "";
        $this->UserFilter = "";
        $this->UserOrderBy = "";
        $this->getSql($this->UseParentFilter, $this->CurrentFilter, $this->LookupFilter, $page); // Call Lookup_Selecting again based on last setting
        $ar = [];
        if ($this->UserSelect != "") {
            $ar["s"] = Encrypt($this->UserSelect);
        }
        if ($this->UserFilter != "") {
            $ar["f"] = Encrypt($this->UserFilter);
        }
        if ($this->UserOrderBy != "") {
            $ar["o"] = Encrypt($this->UserOrderBy);
        }
        if (count($ar) > 0) {
            return '<input type="hidden" id="' . $var . '" name="' . $var . '" value="' . http_build_query($ar) . '">';
        }
        return "";
    }

    /**
     * Output client side list
     *
     * @param PageInterface $page Page
     * @return array
     */
    public function toClientList(PageInterface $page): array
    {
        return [
            "page" => GetClassShortName($page),
            "field" => $this->Field->Name,
            "linkField" => $this->LinkField,
            "displayFields" => $this->DisplayFields,
            "groupByField" => $this->GroupByField,
            "parentFields" => $page->PageID != "grid" && $this->hasParentTable() ? [] : $this->ParentFields,
            "childFields" => $this->ChildFields,
            "filterFields" => $page->PageID != "grid" && $this->hasParentTable() ? [] : array_keys($this->FilterFields),
            "filterFieldVars" => $page->PageID != "grid" && $this->hasParentTable() ? [] : $this->FilterFieldVars,
            "ajax" => $this->LinkTable != "",
            "autoFillTargetFields" => $this->AutoFillTargetFields,
            "template" => $this->Template
        ];
    }

    /**
     * Execute SQL and write JSON response
     *
     * @param ?PageInterface $page Page
     * @return array
     */
    public function toJson(?PageInterface $page = null): array
    {
        if ($page === null) {
            return [];
        }

        // Handle boolean field user values
        if ($this->Field->isBoolean() && $this->Field->OptionCount > 0) {
            return ["result" => "OK", "records" => $this->Field->options(false), "totalRecordCount" => $this->Field->OptionCount];
        }

        // Get table object
        $tbl = $this->getTable();
        if ($tbl) { // Load lookup table permissions (including User IDs)
            Security()->loadTablePermissions($tbl->TableVar);
        }

        // Check if dashboard report / lookup to report source table
        $isReport = $page->TableReportType == "dashboard"
            ? ($tbl->TableType == "REPORT")
            : ($page instanceof ReportTable && in_array($tbl->TableVar, [$page->ReportSourceTable, $page->TableVar]));

        // Set renderer
        $this->renderer = $isReport ? $page : $tbl;

        // Update expression for grouping fields (reports)
        if ($isReport) {
            foreach ($this->DisplayFields as $i => $displayField) {
                if (!IsEmpty($displayField)) {
                    $pageDisplayField = $page->Fields[$displayField] ?? null;
                    $tblDisplayField = $tbl->Fields[$displayField] ?? null;
                    if ($pageDisplayField && $tblDisplayField && !IsEmpty($pageDisplayField->LookupExpression)) {
                        if (!IsEmpty($this->UserOrderBy)) {
                            $this->UserOrderBy = str_replace($tblDisplayField->Expression, $pageDisplayField->LookupExpression, $this->UserOrderBy);
                        }
                        $tblDisplayField->Expression = $pageDisplayField->LookupExpression;
                        $this->Distinct = true; // Use DISTINCT for grouping fields
                    }
                }
            }
        }
        $filterValues = count($this->FilterValues) > 0 ? array_slice($this->FilterValues, 1) : [];
        $useParentFilter = count($filterValues) == count(array_filter($filterValues)) || !$this->hasParentTable() && $this->LookupType != "filter";
        $pageSize = $this->PageSize;
        $offset = $this->Offset;
        $qb = $this->getSqlBuilder($useParentFilter, "", "", $page, !$useParentFilter);
        if (!$qb) {
            return [];
        }
        $recordCnt = ($pageSize > 0) ? $tbl->getRecordCount($qb) : 0; // Get record count first
        $records = [];
        try {
            $records = $this->executeQuery($qb, $pageSize, $offset);
        } catch (Exception $e) {
            if (Config("DEBUG")) {
                LogError($e->getMessage(), ["sql" => $qb->getSQL(), "pageSize" => $pageSize, "offset" => $offset]);
            }
        }
        if (is_array($records)) {
            $rowCnt = count($records);
            $totalCnt = ($pageSize > 0) ? $recordCnt : $rowCnt;

            // Output
            $rows = [];
            foreach ($records as $row) {
                if (SameText($this->LookupType, "autofill")) {
                    $rows[] = $this->renderEditRow($row);
                } elseif ($this->LookupType != "unknown") { // Format display fields for known lookup type
                    $rows[] = $this->renderViewRow($row);
                } else {
                    $rows[] = $row;
                }
            }

            // Set up advanced filter (reports)
            if ($isReport) {
                if (in_array($this->LookupType, ["updateoption", "modal", "autosuggest"])) {
                    if (method_exists($page, "pageFilterLoad")) {
                        $page->pageFilterLoad();
                    }
                    $linkField = $page->Fields[$this->LinkField] ?? null;
                    if ($linkField && is_array($linkField->AdvancedFilters)) {
                        $ar = [];
                        foreach ($linkField->AdvancedFilters as $filter) {
                            if ($filter->Enabled) {
                                $ar[] = ["lf" => $filter->ID, "df" => $filter->Name];
                            }
                        }
                        $rows = array_merge($ar, $rows);
                    }
                }
            }
            $result = ["result" => "OK", "records" => $rows, "totalRecordCount" => $totalCnt];
            if (IsDebug()) {
                $result["sql"] = $qb->getSQL();
            }
            return $result;
        }
        return [];
    }

    /**
     * Get renderer
     *
     * @return object Renderer
     */
    public function getRenderer(): ?object
    {
        return $this->renderer == null || $this->renderer->PageID == "dashboard"
            ? $this->getTable()
            : $this->renderer;
    }

    /**
     * Render edit row
     *
     * @param array $row Input data
     * @return array Output data
     */
    public function renderEditRow(array $row): array
    {
        if ($this->rendering || !$this->FormatAutoFill) { // Avoid recursive calls / Skip format
            return $row;
        }

        // Render data
        $renderer = $this->getRenderer();
        if ($renderer instanceof LookupTableInterface) {
            $this->rendering = true;

            // Lookup for autofill field names
            foreach (array_filter($this->AutoFillSourceFields) as $idx => $name) {
                $autoFillSourceField = $renderer->Fields[$name] ?? null;
                if ($autoFillSourceField && !$autoFillSourceField->AutoFillOriginalValue) {
                    $af = "af" . $idx;
                    if (IsFloatType($autoFillSourceField->Type)) {
                        $row[$af] = $renderer->renderLookupForEdit($name, (float)$row[$af]);
                    } else {
                        $row[$af] = $renderer->renderLookupForEdit($name, $row[$af]);
                    }
                }
            }
            $this->rendering = false;
        }
        return $row;
    }

    /**
     * Render view row
     *
     * @param array $row Input data
     * @return array Output data
     */
    public function renderViewRow(array $row): array
    {
        if ($this->rendering || !$this->FormatLookup) { // Avoid recursive calls / Skip format
            return $row;
        }

        // Render data
        $renderer = $this->getRenderer();
        if ($renderer instanceof LookupTableInterface) {
            $this->rendering = true;

            // Lookup for display field names
            $sameTable = $renderer->TableName == $this->getTable()->TableName;
            foreach (array_filter($this->DisplayFields) as $idx => $name) {
                $displayField = $renderer->Fields[$name] ?? null;
                $df = "df" . ($idx > 0 ? $idx + 1 : "");
                $viewValue = $renderer->renderLookupForView($name, $row[$df]);
                // Make sure that ViewValue is not empty and not self lookup field (except Date/Time) and not field with user values
                if (
                    !IsEmpty($viewValue)
                    && $displayField
                    && !($sameTable && $name == $this->Field->Name && !in_array($displayField->DataType, [DataType::DATE, DataType::TIME]) && $displayField->OptionCount == 0)
                ) {
                    $row[$df] = $viewValue;
                }
            }
            $this->rendering = false;
        }
        return $row;
    }

    /**
     * Get table object
     *
     * @return ?BaseDbTable
     */
    public function getTable(): ?BaseDbTable
    {
        if ($this->LinkTable == "") {
            return null;
        }
        $this->Table ??= Container($this->LinkTable);
        return $this->Table;
    }

    /**
     * Has parent table
     *
     * @return bool
     */
    public function hasParentTable(): bool
    {
        return is_array($this->ParentFields)
            ? array_any($this->ParentFields, fn($parentField) => !IsEmpty($parentField) && str_contains($parentField, " "))
            : false;
    }

    /**
     * Get part of lookup SQL
     *
     * @param string $part Part of the SQL (select|where|orderby|"")
     * @param bool $isUser Whether the CurrentFilter, UserFilter and UserSelect properties should be used
     * @param bool $useParentFilter Use parent filter
     * @param bool $skipFilterFields Skip filter fields
     * @return string|QueryBuilder|null Part of SQL, or QueryBuilder if $part unspecified
     */
    protected function getSqlPart(string $part = "", bool $isUser = true, bool $useParentFilter = true, bool $skipFilterFields = false): string|QueryBuilder|null
    {
        $tbl = $this->getTable();
        if ($tbl === null) {
            return Empty($part) ? null : "";
        }

        // Set up SELECT ... FROM ...
        $dbid = $tbl->Dbid;
        $queryBuilder = $tbl->getQueryBuilder();

        // Group By field
        $groupByField = $tbl->Fields[$this->GroupByField] ?? null;

        // Handle User SELECT first
        $userSelect = "";
        if ($this->UserSelect != "" && $isUser) {
            $pattern = '/^SELECT\s([\s\S]+?)\sFROM\s/i'; 
            $userSelect = $this->UserSelect;
            // Look for pattern SELECT ... FROM ...
            if (preg_match($pattern, $userSelect, $m)) {
                $queryBuilder->select($m[1]);
                $queryBuilder->from(preg_replace($pattern, "", $userSelect));
            } else { // Cannot handle
                $userSelect = "";
            }
        }

        // Build default query if not UserSelect
        if ($userSelect == "") {
            if ($this->Distinct) {
                $queryBuilder->distinct();
            }

            // Set up link field
            $linkField = $tbl->Fields[$this->LinkField] ?? null;
            if (!$linkField) {
                return "";
            }
            $select = $linkField->Expression;
            if ($this->LookupType != "unknown") { // Known lookup types
                $select .= " AS " . QuotedName("lf", $dbid);
            }
            $queryBuilder->select($select);

            // Set up lookup fields
            $lookupCnt = 0;
            if (SameText($this->LookupType, "autofill")) {
                if (is_array($this->AutoFillSourceFields)) {
                    foreach ($this->AutoFillSourceFields as $i => $autoFillSourceField) {
                        $autoFillSourceField = $tbl->Fields[$autoFillSourceField] ?? null;
                        if (!$autoFillSourceField) {
                            $select = "'' AS " . QuotedName("af" . $i, $dbid);
                        } else {
                            $select = $autoFillSourceField->Expression . " AS " . QuotedName("af" . $i, $dbid);
                        }
                        $queryBuilder->addSelect($select);
                        if (!$autoFillSourceField->AutoFillOriginalValue && $this->FormatAutoFill == null) { // If not explicitly set to false
                            $this->FormatAutoFill = true;
                        }
                        $lookupCnt++;
                    }
                }
            } else {
                if (is_array($this->DisplayFields)) {
                    foreach ($this->DisplayFields as $i => $displayField) {
                        $displayField = $tbl->Fields[$displayField] ?? null;
                        if (!$displayField) {
                            $select = "'' AS " . QuotedName("df" . ($i == 0 ? "" : $i + 1), $dbid);
                        } else {
                            $select = $displayField->Expression;
                            if ($this->LookupType != "unknown") { // Known lookup types
                                $select .= " AS " . QuotedName("df" . ($i == 0 ? "" : $i + 1), $dbid);
                            }
                        }
                        $queryBuilder->addSelect($select);
                        $lookupCnt++;
                    }
                }
                if (is_array($this->FilterFields) && !$useParentFilter && !$skipFilterFields) {
                    $i = 0;
                    foreach ($this->FilterFields as $filterField => $filterOpr) {
                        $filterField = $tbl->Fields[$filterField] ?? null;
                        if (!$filterField) {
                            $select = "'' AS " . QuotedName("ff" . ($i == 0 ? "" : $i + 1), $dbid);
                        } else {
                            $select = $filterField->Expression;
                            if ($this->LookupType != "unknown") { // Known lookup types
                                $select .= " AS " . QuotedName("ff" . ($i == 0 ? "" : $i + 1), $dbid);
                            }
                        }
                        $queryBuilder->addSelect($select);
                        $i++;
                        $lookupCnt++;
                    }
                }
                if ($groupByField) {
                    $select = $this->GroupByExpression;
                    if ($this->LookupType != "unknown") { // Known lookup types
                        $select .= " AS " . QuotedName("gf", $dbid);
                    }
                    $queryBuilder->addSelect($select);
                }
            }
            if ($lookupCnt == 0) {
                return "";
            }
            $queryBuilder->from($tbl->getSqlFrom());
        }

        // Set up WHERE
        $where = "";

        // Set up user id filter
        if (method_exists($tbl, "applyUserIDFilters")) {
            $where = $tbl->applyUserIDFilters($where, "lookup");
        }

        // Set up table filter for filter fields
        if ($this->UseTableFilterForFilterFields && $this->LookupType == "filter" && method_exists($tbl, "getDefaultFilter")) {
            AddFilter($where, $tbl->getDefaultFilter());
        }

        // Set up current filter
        $cnt = count($this->FilterValues);
        if ($cnt > 0 && !(SameText($this->LookupType, "updateoption") && $this->IsAutoFillTargetField)) { // Load all records if IsAutoFillTargetField
            $val = $this->FilterValues[0];
            if ($val != "") {
                $val = strval($val);
                if ($linkField->DataType == DataType::GUID && !CheckGuid($val)) {
                    AddFilter($where, "1=0"); // Disallow
                } else {
                    AddFilter($where, $this->getFilter($linkField, "=", $val, $tbl->Dbid));
                }
            }

            // Set up parent filters
            if (is_array($this->FilterFields) && $useParentFilter && !($isUser && preg_match('/\{v(\d)\}/i', $this->UserFilter))) { // UserFilter does not contain ({v<n>})
                $i = 1;
                foreach ($this->FilterFields as $filterField => $filterOpr) {
                    if ($filterField != "") {
                        $filterField = $tbl->Fields[$filterField] ?? null;
                        if (!$filterField) {
                            return "";
                        }
                        if ($cnt <= $i) {
                            AddFilter($where, "1=0"); // Disallow
                        } else {
                            $val = strval($this->FilterValues[$i]);
                            AddFilter($where, $this->getFilter($filterField, $filterOpr, $val, $tbl->Dbid));
                        }
                    }
                    $i++;
                }
            }
        }

        // Set up search
        if ($this->SearchValue != "") {
            // Normal autosuggest
            if (SameText($this->LookupType, "autosuggest") && !$this->LookupAllDisplayFields) {
                AddFilter($where, $this->getAutoSuggestFilter($this->SearchValue, $tbl->Dbid));
            } else { // Use quick search logic
                AddFilter($where, $this->getModalSearchFilter($this->SearchValue, $tbl->Dbid));
            }
        }

        // Add filters
        if ($this->CurrentFilter != "" && $isUser) {
            AddFilter($where, $this->CurrentFilter);
        }

        // User Filter
        if ($this->UserFilter != "" && $isUser) {
            AddFilter($where, $this->getUserFilter());
        }

        // Set up ORDER BY
        $orderBy = $this->UserOrderBy;
        if ($groupByField) { // Sort GroupByField first
            if (StartsString("(", $this->GroupByExpression) && EndsString(")", $this->GroupByExpression)) {
                $groupByExpression = QuotedName("gf", $dbid);
            } else {
                $groupByExpression = $this->GroupByExpression;
            }
            $orderBy = $groupByExpression . " ASC" . (IsEmpty($orderBy) ? "" : ", " . $orderBy);
        }

        // Return SQL part
        if ($part == "select") {
            return $userSelect != "" ? $userSelect : $queryBuilder->getSQL();
        } elseif ($part == "where") {
            return $where;
        } elseif ($part == "orderby") {
            return $orderBy;
        } else {
            if ($where != "") {
                $queryBuilder->where($where);
            }
            $flds = GetSortFields($orderBy);
            if (is_array($flds)) {
                foreach ($flds as $fld) {
                    $queryBuilder->addOrderBy($fld[0], $fld[1]);
                }
            }
            return $queryBuilder;
        }
    }

    /**
     * Get user filter
     *
     * @return string
     */
    protected function getUserFilter(): string
    {
        $filter = $this->UserFilter;
        if (preg_match_all('/\{v(\d)\}/i', $filter, $matches, PREG_SET_ORDER)) { // Match {v<n>} to FilterValues
            foreach ($matches as $match) {
                $index = intval($match[1]);
                $value = $this->FilterValues[$index] ?? null;
                if (!IsEmpty($value)) { // Replace {v<n>}
                    $filter = str_replace($match[0], AdjustSql($value), $filter);
                } else { // No filter value found, ignore filter
                    Log("Value for {$match[0]} not found.");
                    return "";
                }
            }
        }
        return $filter;
    }

    /**
     * Get filter
     *
     * @param DbField $fld Field Object
     * @param string $opr Search Operator
     * @param string $val Search Value
     * @param string $dbid Database ID
     * @return string Search Filter (SQL WHERE part)
     */
    protected function getFilter(DbField $fld, string $opr, string $val, string $dbid = "DB"): string
    {
        $valid = $val != "";
        $where = "";
        $ar = $this->Field->HtmlTag == "SELECT" && $this->Field->SelectMultiple || $this->Field->HtmlTag == "CHECKBOX" // DO NOT use $this->Field->isMultiSelect() which must be string type field
            ? explode(Config("MULTIPLE_OPTION_SEPARATOR"), $val)
            : [$val];
        if ($fld->DataType == DataType::NUMBER) { // Validate numeric fields
            foreach ($ar as $val) {
                if (!is_numeric($val)) {
                    $valid = false;
                }
            }
        }
        if ($valid) {
            if ($opr == "=") { // Use the IN operator
                foreach ($ar as &$val) {
                    $val = QuotedValue($val, $fld, $dbid);
                }
                $where = $fld->Expression . " IN (" . implode(", ", $ar) . ")";
            } else { // Custom operator
                $dbtype = GetConnectionType($dbid);
                foreach ($ar as $val) {
                    if (in_array($opr, ["LIKE", "NOT LIKE", "STARTS WITH", "ENDS WITH"])) {
                        $fldOpr = ($opr == "NOT LIKE") ? "NOT LIKE" : "LIKE";
                        $filter = LikeOrNotLike($fldOpr, Wildcard($val, $opr, $dbid), $dbid);
                    } else {
                        $fldOpr = $opr;
                        $val = QuotedValue($val, $fld, $dbid);
                        $filter = $fld->Expression . $fldOpr . $val;
                    }
                    AddFilter($where, $filter, "OR");
                }
            }
        } else {
            $where = "1=0"; // Disallow
        }
        return $where;
    }

    /**
     * Get Where part
     *
     * @return string
     */
    protected function getWhere(bool $useParentFilter = false): string
    {
        return $this->getSqlPart("where", false, $useParentFilter);
    }

    /**
     * Execute query
     *
     * @param QueryBuilder $qb QueryBuilder to be executed
     * @param int $pageSize
     * @param int $offset
     * @return array
     */
    protected function executeQuery(QueryBuilder $qb, int $pageSize, int $offset): array
    {
        $tbl = $this->getTable();
        if ($tbl === null) {
            return null;
        }
        if ($offset > -1) {
            $qb->setFirstResult($offset);
        }
        if ($pageSize > 0) {
            $qb->setMaxResults($pageSize);
        }
        return $tbl->getLookupResult($qb);
    }

    /**
     * Get search expression
     *
     * @return string
     */
    protected function getSearchExpression(): string
    {
        if (IsEmpty($this->SearchExpression)) {
            $tbl = $this->getTable();
            $displayField = $tbl->Fields[$this->DisplayFields[0]] ?? null;
            if ($displayField) {
                $this->SearchExpression = $displayField->Expression;
            }
        }
        return $this->SearchExpression;
    }

    /**
     * Get auto suggest filter
     *
     * @param string $sv Search value
     * @return string
     */
    protected function getAutoSuggestFilter(string $sv, string $dbid = "DB"): string
    {
        return $this->getSearchExpression() . Like(Wildcard($sv, "STARTS WITH", $dbid), $dbid);
    }

    /**
     * Get modal search filter
     *
     * @param string $sv Search value
     * @param array $dbid Database ID
     * @return string
     */
    protected function getModalSearchFilter(string $sv, string $dbid = "DB"): string
    {
        if (IsEmpty($sv)) {
            return "";
        }
        $search = trim($sv);
        $searchType = self::$ModalLookupSearchType;
        $ar = GetQuickSearchKeywords($search, $searchType);
        $filter = "";
        foreach ($ar as $keyword) {
            if ($keyword != "") {
                $thisFilter = $this->getSearchExpression() . Like(Wildcard($keyword, self::$ModalLookupSearchOperator, $dbid), $dbid);
                AddFilter($filter, $thisFilter, $searchType);
            }
        }
        return $filter;
    }

    /**
     * Format options
     *
     * @param array $options Input options with formats:
     *  1. Manual input data, e.g. [ ["lv", "dv", "dv2", "dv3", "dv4"], ["lv", "dv", "dv2", "dv3", "dv4"], ... ]
     *  2. Data from database, e.g. [ ["Field1" => "lv", "Field2" => "dv", ...], ["Field1" => "lv", "Field2" => "dv", ...], ... ]
     * @return array ["lv" => ["lf" => "lv", "df" => "dv", ...], ...]
     */
    protected function formatOptions(array $options): array
    {
        if (!is_array($options)) {
            return null;
        }
        $keys = ["lf", "df", "df2", "df3", "df4", "ff", "ff2", "ff3", "ff4"];
        $opts = [];
        $cnt = count($keys);

        // Check values
        foreach ($options as &$ar) {
            if (is_array($ar)) {
                if ($cnt > count($ar)) {
                    $cnt = count($ar);
                }
            }
        }

        // Set up options
        if ($cnt >= 2) {
            $keys = array_splice($keys, 0, $cnt);
            foreach ($options as &$ar) {
                if (is_array($ar)) {
                    $ar = array_splice($ar, 0, $cnt);
                    $ar = array_combine($keys, $ar); // Set keys
                    $lv = $ar["lf"]; // First value as link value
                    $opts[$lv] = $ar;
                }
            }
        } else {
            return null;
        }
        return $opts;
    }
}
