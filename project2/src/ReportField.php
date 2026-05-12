<?php

namespace PHPMaker2026\Project1;

use Illuminate\Support\Collection;

/**
 * Report field class
 */
class ReportField extends DbField
{
    public string $SourceTableVar = ""; // Source Table variable name
    public ?float $SumValue = 0; // Sum
    public ?float $AverageValue = 0; // Average
    public ?float $MinimumValue = null; // Minimum
    public ?float $MaximumValue = null; // Maximum
    public ?int $CountValue = 0; // Count
    public mixed $SumViewValue = null; // Sum
    public mixed $AverageViewValue = null; // Average
    public mixed $MinimumViewValue = null; // Minimum
    public mixed $MaximumViewValue = null; // Maximum
    public mixed $CountViewValue = null; // Count
    public string $DrillDownTable = ""; // Drill down table name
    public string $DrillDownUrl = ""; // Drill down URL
    public string $CurrentFilter = ""; // Current filter in use
    public int $GroupingFieldId = 0; // Grouping field id
    public bool $ShowGroupHeaderAsRow = false; // Show grouping level as row
    public bool $ShowCompactSummaryFooter = true; // Show compact summary footer
    public string $GroupByType = ""; // Group By Type
    public string $GroupInterval = ""; // Group Interval
    public string $GroupSql = ""; // Group SQL
    public mixed $GroupValue = null; // Group Value
    public mixed $GroupViewValue = null; // Group View Value
    public string $DateFilter = ""; // Date Filter ("year"|"quarter"|"month"|"day"|"")
    public string $Delimiter = ""; // Field delimiter (e.g. comma) for delimiter separated value
    public array $DistinctValues = [];
    public array $Records = [];
    public bool $LevelBreak = false;
    public bool $Expanded = true;
    public array $DashboardSearchSourceFields = [];
    public string $SearchType = "";

    // Database value (override)
    public function setDbValue(mixed $v): static
    {
        if ($this->Type == 131 || $this->Type == 139) { // Convert adNumeric/adVarNumeric field
            $v = floatval($v);
        }
        return parent::setDbValue($v); // Call parent method
    }

    // Group value
    public function groupValue(): mixed
    {
        return $this->GroupValue;
    }

    // Set group value
    public function setGroupValue(mixed $v): void
    {
        $this->setDbValue($v);
        $this->GroupValue = $this->DbValue;
    }

    // Get distinct values
    public function getDistinctValues(array $records, string $sort = "ASC"): void
    {
        $name = $this->getGroupName();
        if (SameText($sort, "DESC")) {
            $this->DistinctValues = Collection::make($records)
                ->sortByDesc($name)
                ->pluck($name)
                ->unique()
                ->all();
        } else {
            $this->DistinctValues = Collection::make($records)
                ->sortBy($name)
                ->pluck($name)
                ->unique()
                ->all();
        }
    }

    // Get distinct records
    public function getDistinctRecords(array $records, mixed $val): void
    {
        $name = $this->getGroupName();
        $this->Records = Collection::make($records)
            ->where($name, $val)
            ->all();
    }

    // Get sum
    public function getSum(array $records, bool $skipNull = false): float
    {
        $name = $this->getGroupName();
        $sum = 0;
        if (count($records) > 0) {
            $collection = $skipNull
                ? Collection::make($records)->whereNotNull($name)
                : Collection::make($records);
            if (!$collection->isEmpty()) {
                $sum = $collection->sum($name);
            }
        }
        $this->SumValue = $sum;
        return $sum;
    }

    // Get average
    public function getAverage(array $records, bool $skipNull = false): float
    {
        $name = $this->getGroupName();
        $avg = 0;
        if (count($records) > 0) {
            $collection = $skipNull
                ? Collection::make($records)->whereNotNull($name)
                : Collection::make($records);
            if (!$collection->isEmpty()) {
                $avg = $collection->avg($name);
            }
        }
        $this->AverageValue = $avg;
        return $avg;
    }

    // Get min
    public function getMin(array $records, bool $skipNull = false): ?float
    {
        $name = $this->getGroupName();
        $min = null;
        if (count($records) > 0) {
            $collection = $skipNull
                ? Collection::make($records)->whereNotNull($name)
                : Collection::make($records);
            if (!$collection->isEmpty()) {
                $min = $collection->min($name);
            }
        }
        $this->MinimumValue = $min;
        return $min;
    }

    // Get max
    public function getMax(array $records, bool $skipNull = false): ?float
    {
        $name = $this->getGroupName();
        $max = null;
        if (count($records) > 0) {
            $collection = $skipNull
                ? Collection::make($records)->whereNotNull($name)
                : Collection::make($records);
            if (!$collection->isEmpty()) {
                $max = $collection->max($name);
            }
        }
        $this->MaximumValue = $max;
        return $max;
    }

    // Get count
    public function getCount(array $records, bool $skipNull = false): int
    {
        $name = $this->getGroupName();
        $cnt = 0;
        if (count($records) > 0) {
            $collection = $skipNull
                ? Collection::make($records)->whereNotNull($name)
                : Collection::make($records);
            $cnt = $collection->count();
        }
        $this->CountValue = $cnt;
        $this->Count = $cnt;
        return $cnt;
    }

    // Get group name
    public function getGroupName(): string
    {
        return $this->GroupSql != "" ? "EW_GROUP_VALUE_" . $this->GroupingFieldId : $this->Name;
    }

    /**
     * Format advanced filters
     *
     * @param mixed $af
     */
    public function formatAdvancedFilters(mixed $af): mixed
    {
        if (is_array($af) && is_array($this->AdvancedFilters)) {
            foreach ($af as &$wrk) {
                $lf = $wrk["lf"] ?? "";
                $df = $wrk["df"] ?? "";
                if (StartsString("@@", $lf) && SameString($lf, $df)) {
                    $key = substr($lf, 2);
                    if (array_key_exists($key, $this->AdvancedFilters)) {
                        $wrk["df"] = $this->AdvancedFilters[$key]->Name;
                    }
                }
            }
        }
        return $af;
    }

    /**
     * Search expression
     *
     * @return string Search expression
     */
    public function searchExpression(): string
    {
        if (!IsEmpty($this->DateFilter)) { // Date filter
            return match (strtolower($this->DateFilter)) {
                "year" => GroupSql($this->Expression, "y", 0, $this->Table->Dbid),
                "quarter" => GroupSql($this->Expression, "q", 0, $this->Table->Dbid),
                "month" => GroupSql($this->Expression, "m", 0, $this->Table->Dbid),
                "week" => GroupSql($this->Expression, "w", 0, $this->Table->Dbid),
                "day" => GroupSql($this->Expression, "d", 0, $this->Table->Dbid),
                "hour" => GroupSql($this->Expression, "h", 0, $this->Table->Dbid),
                "minute" => GroupSql($this->Expression, "min", 0, $this->Table->Dbid),
                default => $this->Expression
            };
        } elseif ($this->GroupSql != "") { // Use grouping SQL for search if exists
            return str_replace("%s", $this->Expression, $this->GroupSql);
        }
        return parent::searchExpression();
    }

    /**
     * Search field type
     *
     * @return enum Search data type
     */
    public function searchDataType(): DataType
    {
        if (!IsEmpty($this->DateFilter)) { // Date filter
            return match (strtolower($this->DateFilter)) {
                "year" => DataType::NUMBER,
                "quarter" => DataType::STRING,
                "month" => DataType::STRING,
                "week" => DataType::STRING,
                "day" => DataType::STRING,
                "hour" => DataType::NUMBER,
                "minute" => DataType::NUMBER,
                default => $this->DataType
            };
        } elseif ($this->GroupSql != "") { // Use grouping SQL for search if exists
            return DataType::STRING;
        }
        return parent::searchDataType();
    }

    /**
     * Group toggle icon
     *
     * @return string Group toggle icon
     */
    public function groupToggleIcon(): string
    {
        $iconClass = "ew-group-toggle fa-solid fa-caret-down" . ($this->Expanded || $this->Table->hideGroupLevel() != $this->GroupingFieldId ? "" : " ew-rpt-grp-hide");
        return '<i class="' . $iconClass . '"></i>';
    }

    /**
     * Expand group
     *
     * @param bool $value Expanded
     */
    public function setExpanded(bool $value): void
    {
        foreach ($this->Table->Fields as $fld) {
            if ($fld->GroupingFieldId >= $this->GroupingFieldId) {
                $fld->Expanded = $value;
            }
        }
    }

    /**
     * Cell attributes
     *
     * @return string Cell attributes
     */
    public function cellAttributes(string $className = ""): string
    {
        if ($className) {
            $this->CellAttrs->appendClass($className);
        }
        $cellAttrs = parent::cellAttributes(); // Call parent method
        if ($className) {
            $this->CellAttrs->removeClass($className);
        }
        return $cellAttrs;
    }
}
