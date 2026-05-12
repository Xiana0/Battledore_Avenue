<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Query\QueryBuilder;
use Dflydev\DotAccessData\Data;
use Spatie\Color\Hex;
use Spatie\Color\Rgb;
use Spatie\Color\Rgba;
use Spatie\Color\Hsl;

/**
 * Chart service class
 */
class ChartService
{
    public ?array $Data = null;
    public ?array $Series = null;

    // Constructor
    public function __construct(
        public DbChart $Chart,
        public ?object $Service = null,
    ) {
    }

    // Get chart data
    public function getChartData(string $where, string $orderBy): array
    {
        global $httpContext;

        // Set up chart base SQL
        $page = CurrentPage();
        $chart = $this->Chart;
        $table = $chart->Table;
        if ($table->TableReportType == "crosstab") { // Crosstab chart
            $sqlSelect = $table->getSqlSelect()->addSelect($this->Service->getSummarySql($where));
        } else {
            $sqlSelect = $table->getSqlSelect();
        }
        $sqlChartSelect = $table->getQueryBuilder()->select($chart->SqlXField, $chart->SqlSeriesField, $chart->SqlYField);
        if ($table->TableType == "REPORT") { // Page_Selecting for reports
            if (method_exists($page, "pageSelecting")) {
                $page->pageSelecting($where);
            }
        } else { // Records_Selecting for tables
            if (method_exists($page, "recordsSelecting")) {
                $page->recordsSelecting($where);
            }
        }
        $dbType = GetConnectionType($table->Dbid);
        if ($table->SourceTableIsCustomView) {
            $sqlChartBase = "(" . $this->buildReportSql($sqlSelect, $table->getSqlFrom(), $table->getSqlWhere(), $table->getSqlGroupBy(), $table->getSqlHaving(), ($dbType == "MSSQL") ? $table->getSqlOrderBy() : "", $where, "")->getSQL() . ") TMP_TABLE";
        } else {
            $sqlChartBase = $table->getSqlFrom();
        }

        // Set up chart series
        if (!IsEmpty($chart->SeriesFieldName)) {
            if ($chart->SeriesType == 1) { // Multiple Y fields
                $ar = explode("|", $chart->SeriesFieldName);
                $cnt = count($ar);
                $yaxis = explode(",", $chart->SeriesYAxis);
                for ($i = 0; $i < $cnt; $i++) {
                    $fld = $table->Fields[$ar[$i]];
                    if (StartsString("4", strval($chart->Type))) { // Combination charts
                        $series = @$yaxis[$i] == "2" ? "y1" : "y";
                        $this->Series[] = [$fld->caption(), $series];
                    } else {
                        $this->Series[] = $fld->caption();
                    }
                }
            } elseif ($table->TableReportType == "crosstab" && $chart->SeriesFieldName == $table->ColumnFieldName && $table->ColumnDateSelection && $table->ColumnDateType == "q") { // Quarter
                for ($i = 1; $i <= 4; $i++) {
                    $this->Series[] = QuarterName($i);
                }
            } elseif ($table->TableReportType == "crosstab" && $chart->SeriesFieldName == $table->ColumnFieldName && $table->ColumnDateSelection && $table->ColumnDateType == "m") { // Month
                for ($i = 1; $i <= 12; $i++) {
                    $this->Series[] = MonthName($i);
                }
            } else { // Load chart series from SQL directly
                $sqlSelectSeries = $table->getQueryBuilder()->select($chart->SqlSeriesField)->distinct();
                if ($table->SourceTableIsCustomView) {
                    $sql = $this->buildReportSql($sqlSelectSeries, $sqlChartBase, $chart->SqlWhereSeries, $chart->SqlGroupBySeries, "", $chart->SqlOrderBySeries, "", "");
                } else {
                    $chartFilter = $chart->SqlWhereSeries;
                    AddFilter($chartFilter, $table->getSqlWhere());
                    $sql = $this->buildReportSql($sqlSelectSeries, $sqlChartBase, $chartFilter, $chart->SqlGroupBySeries, "", $chart->SqlOrderBySeries, $where, "");
                }
                $chart->ChartSeriesSql = $sql->getSQL();
            }
        }

        // Run time sort, update SqlOrderBy
        if ($chart->RunTimeSort) {
            $chart->SqlOrderBy .= ($chart->SortType == 2) ? " DESC" : "";
        }

        // Set up ChartSql
        if ($table->SourceTableIsCustomView) {
            $sql = $this->buildReportSql($sqlChartSelect, $sqlChartBase, $chart->SqlWhere, $chart->SqlGroupBy, "", $chart->SqlOrderBy, "", "");
        } else {
            $chartFilter = $chart->SqlWhere;
            AddFilter($chartFilter, $table->getSqlWhere());
            $sql = $this->buildReportSql($sqlChartSelect, $sqlChartBase, $chartFilter, $chart->SqlGroupBy, "", $chart->SqlOrderBy, $where, "");
        }
        $chart->ChartSql = $sql->getSQL();
        $this->loadChartData();
        return [ "data" => $this->Data, "series" => $this->Series ];
    }

    // Load data
    public function loadChartData(): void
    {
        // Setup chart series data
        $chart = $this->Chart;
        if ($chart->ChartSeriesSql != "") {
            $this->loadSeries();
            if (IsDebug()) {
                LogInfo("(Chart Series SQL): " . $chart->ChartSeriesSql);
            }
        }

        // Setup chart data
        if ($chart->ChartSql != "") {
            $this->loadData();
            if (IsDebug()) {
                LogInfo("(Chart SQL): " . $chart->ChartSql);
            }
        }
    }

    // Load Chart Series
    public function loadSeries(): void
    {
        $chart = $this->Chart;
        $table = $chart->Table;
        $sql = $chart->ChartSeriesSql;
        $cnn = Conn($table->Dbid);
        $sdt = $chart->SeriesDateType;
        $rows = $cnn->executeQuery($sql)->fetchAllNumeric();
        foreach ($rows as $row) {
            $this->Series[] = $chart->seriesValue($row[0], $sdt); // Series value
        }
    }

    // Load Chart Data from SQL
    public function loadData(): void
    {
        $chart = $this->Chart;
        $table = $chart->Table;
        $sql = $chart->ChartSql;
        $cnn = Conn($table->Dbid);
        $rows = $cnn->executeQuery($sql)->fetchAllNumeric();
        foreach ($rows as $row) {
            if ($chart->ShowLookupForXAxis) {
                $row = $table->renderChartXAxis($chart->ChartVar, $row);
            }
            $this->Data[] = $row;
        }
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
     * @param string $sort
     * @return QueryBuilder
     */
    public function buildReportSql(string|QueryBuilder $select, string $from, string $where, string $groupBy = "", string $having = "", string $orderBy = "", string $filter = "", string $sort = ""): QueryBuilder
    {
        if (is_string($select)) {
            $queryBuilder = $this->getQueryBuilder();
            $queryBuilder->select($select);
        } elseif ($select instanceof QueryBuilder) {
            $queryBuilder = $select;
        }
        if ($from != "") {
            $queryBuilder->from($from);
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
}
