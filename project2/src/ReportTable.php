<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Query\QueryBuilder;
use ParagonIE\CSPBuilder\CSPBuilder;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Report table class
 */
class ReportTable extends BaseDbTable
{
    // Data to be passed to Custom Template
    public static array $CustomTemplateDatatypes = [
        DataType::NUMBER,
        DataType::DATE,
        DataType::STRING,
        DataType::BOOLEAN,
        DataType::TIME
    ];
    public string $ReportSourceTable = "";
    public string $ReportServiceClass = "";
    public RowSummary $RowTotalType = RowSummary::DETAIL; // Row summary type
    public RowTotal $RowTotalSubType = RowTotal::HEADER; // Row total type
    public int $RowGroupLevel = 0; // Row group level
    public bool $ShowReport = true;

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
        $this->ShowDrillDownFilter = Config("SHOW_DRILLDOWN_FILTER");
        $this->UseDrillDownPanel = Config("USE_DRILLDOWN_PANEL");
    }

    // Session Group Per Page
    public function getGroupPerPage(): int
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_grpperpage")) ?? 0;
    }

    public function setGroupPerPage(int $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_grpperpage"), $v);
    }

    // Session Start Group
    public function getStartGroup(): int
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_start")) ?? 0;
    }

    public function setStartGroup(int $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_start"), $v);
    }

    // Session Order By
    public function getOrderBy(): string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_orderby")) ?? "";
    }

    public function setOrderBy(string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_orderby"), $v);
    }

    // Session Order By (for non-grouping fields)
    public function getDetailOrderBy(): string
    {
        return Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_detailorderby")) ?? "";
    }

    public function setDetailOrderBy(string $v): void
    {
        Session(AddTabId(PROJECT_NAME . "_" . $this->TableVar . "_detailorderby"), $v);
    }

    // Reset attributes for table object
    public function resetAttributes(): void
    {
        $this->RowAttrs = new Attributes();
        $this->Fields->resetAttributes();
    }

    /**
     * Build Report SQL
     *
     * @param string|QueryBuilder $select
     * @param string $from
     * @param string $where
     * @param string $groupBy
     * @param string $having
     * @param string $orderBy
     * @param string $filter
     * @param string|array $sort
     * @return QueryBuilder
     */
    public function buildReportSql(string|QueryBuilder $select, string $from, string $where, string $groupBy = "", string $having = "", string $orderBy = "", string $filter = "", string|array $sort = ""): QueryBuilder
    {
        if (is_string($select)) {
            $queryBuilder = $this->getQueryBuilder()->select($select);
        } elseif ($select instanceof QueryBuilder) {
            $queryBuilder = $select;
        }
        if ($from != "") {
            $queryBuilder = $queryBuilder->from($from);
        }
        if ($where != "") {
            $queryBuilder->where($where);
        }
        if ($filter != "") {
            $queryBuilder->andWhere($filter);
        }
        if ($groupBy != "") {
            $queryBuilder->groupBy($groupBy);
        }
        if ($having != "") {
            $queryBuilder->having($having);
        }
        $flds = UpdateSortFields($orderBy, $sort, 1);
        if (is_array($flds)) {
            foreach ($flds as $fld) {
                $queryBuilder->addOrderBy($fld[0], $fld[1]);
            }
        }
        return $queryBuilder;
    }

    /**
     * Report row attributes
     *
     * @return string Row Attributes
     */
    public function rowAttributes(): string
    {
        $level = $this->hideGroupLevel();
        $hide = $level > 0;
        if ($hide && $this->RowGroupLevel == $level && $this->RowType == RowType::TOTAL && $this->RowTotalSubType == RowTotal::HEADER) { // Do not hide current grouping header
            $hide = false;
        }
        if ($hide) {
            $this->RowAttrs->appendClass("ew-rpt-grp-hide-" . $level);
        }
        $attrs = parent::rowAttributes();
        if ($hide) {
            $this->RowAttrs->removeClass("ew-rpt-grp-hide-" . $level);
        }
        return $attrs;
    }

    /**
     * Hide group level
     *
     * @return int Hide group level
     */
    public function hideGroupLevel(): int
    {
        $fields = array_filter($this->Fields->getArrayCopy(), fn($fld) => $fld->GroupingFieldId > 0); // Get all grouping fields
        usort($fields, fn($f1, $f2) => $f1->GroupingFieldId - $f2->GroupingFieldId); // Sort by GroupingFieldId
        foreach ($fields as $fld) {
            if (!$fld->Expanded && $fld->GroupingFieldId <= $this->RowGroupLevel) {
                return $fld->GroupingFieldId;
            }
        }
        return 0;
    }

    // Register filter group
    public function registerFilterGroup(ReportField &$fld, string $groupName): void
    {
        $filters = Config("REPORT_ADVANCED_FILTERS." . $groupName) ?: [];
        foreach ($filters as $id => $functionName) {
            $this->registerFilter($fld, "@@" . $id, Language()->phrase($id), $functionName);
        }
    }

    // Register filter
    public function registerFilter(ReportField &$fld, string $id, string $name, string $functionName = ""): void
    {
        if (!is_array($fld->AdvancedFilters)) {
            $fld->AdvancedFilters = [];
        }
        $wrkid = StartsString("@@", $id) ? $id : "@@" . $id;
        $key = substr($wrkid, 2);
        $fld->AdvancedFilters[$key] = new AdvancedFilter($wrkid, $name, $functionName);
    }

    // Unregister filter
    public function unregisterFilter(ReportField &$fld, string $id): void
    {
        if (is_array($fld->AdvancedFilters)) {
            $wrkid = StartsString("@@", $id) ? $id : "@@" . $id;
            $key = substr($wrkid, 2);
            foreach ($fld->AdvancedFilters as $filter) {
                if ($filter->ID == $wrkid) {
                    unset($fld->AdvancedFilters[$key]);
                    break;
                }
            }
        }
    }

    // Convert row for Custom Template
    public function ConvertRowForCustomTemplate($row): array
    {
        $values = [];
        foreach ($row as $fldname => $value) {
            $fld = $this->Fields[$fldname] ?? null;
            if ($fld && in_array($fld->DataType, static::$CustomTemplateDatatypes) && $fld->Visible) {
                $values[$fld->Param] = $value;
            } else {
                $values[$fldname] = $value;
            }
        }
        return $values;
    }
}
