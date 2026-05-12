<?php

namespace PHPMaker2026\Project1;

use Dflydev\DotAccessData\Data;

/**
 * Chart.js renderer
 */
class ChartJsRenderer implements ChartRendererInterface
{
    public DbChart $Chart;
    public array $Data = [];
    public static int $DefaultWidth = 600;
    public static int $DefaultHeight = 500;
    public static float $DefaultBorderWidth = 1.5;
    public static bool $DefaultResponsive = true;
    public static bool $DefaultMaintainAspectRatio = true;

    // Constructor
    public function __construct(public Data $Options = new Data())
    {
    }

    // Set chart
    public function setChart(DbChart $chart): static
    {
        $this->Chart = $chart;
        return $this;
    }

    // Get chart
    public function getChart(): DbChart
    {
        return $this->Chart;
    }

    // Get chart Canvas
    public function getContainer(?int $width, ?int $height): string
    {
        $id = $this->Chart->ID; // Chart ID
        $widthAttr = $width !== null ? ' width="' . $width . '"' : '';
        $heightAttr = $height !== null ? ' height="' . $height . '"' : '';
        return '<div id="div_' . $id . '" class="ew-chart-container"><canvas id="chart_' . $id . '"' . $widthAttr . $heightAttr . ' class="ew-chart-canvas"></canvas></div>';
    }

    // Get chart JavaScript
    public function getScript(): string
    {
        $drilldown = $this->Chart->DrillDownInPanel;
        $typ = $this->Chart->Type ?: ChartTypes::$DefaultType; // Chart type (nnnn)
        $id = $this->Chart->ID; // Chart ID
        // $scroll = $this->Chart->ScrollChart; // Not supported
        // $trends = $this->Chart->Trends;
        // $series = $this->Chart->Series;
        // $align = $this->Chart->Align;
        $chartType = ChartTypes::getName($typ); // Chart type name
        $canvasId = "chart_" . $id;
        $this->loadChart();
        $chartData = ["type" => $chartType, "data" => $this->Data, "options" => $this->Options->export()];

        // Output JavaScript for Chart.js
        $yFieldFormat = $this->Chart->YFieldFormat;
        $yAxisFormat = $this->Chart->YAxisFormat;
        $args = [
            "id" => $id,
            "canvasId" => $canvasId,
            "chartJson" => $chartData,
            "yFieldFormat" => $yFieldFormat,
            "yAxisFormat" => $yAxisFormat,
            "useDrilldownPanel" => null
        ];
        if ($this->Chart->DrillDownUrl != "") {
            $args["useDrilldownPanel"] = $this->Chart->UseDrillDownPanel;
        }
        if ($this->Chart->isPieChart() || $this->Chart->isDoughnutChart()) {
            $args["showPercentage"] = $this->Chart->ShowPercentage;
        }
        $wrk = '<script' . Nonce() . '>ew.createChart(' . json_encode($args) . ');</script>';

        // Show data for debug
        if (IsDebug()) {
            $chartJson = json_encode($chartData, JSON_PRETTY_PRINT); // Pretty print
            LogInfo("Chart JSON: " . $chartJson);
        }
        return $wrk;
    }

    // Load chart
    protected function loadChart(): void
    {
        $chtType = $this->Chart->loadParameter("type");
        $chartSeries = $this->Chart->Series;
        $chartData = $this->Chart->DisplayData;
        $multiSeries = $this->Chart->isSingleSeries() ? 0 : 1; // $multiSeries = 1 (Multi series charts)
        $seriesType = $this->Chart->loadParameter("seriestype");

        // Load default options
        $this->Options->import($this->Chart->getParameters("options"));

        // chartjs-plugin-datalabels options
        // https://chartjs-plugin-datalabels.netlify.app/guide/options.html
        $this->Options["plugins.datalabels.clamp"] ??= true;
        $this->Options["plugins.datalabels.display"] ??= "auto";
        $title = $this->Chart->loadParameter("caption");

        // Set dataset.circular to true for Pie/Doughnut/Polar Area chart
        if ($this->Chart->isPieChart() || $this->Chart->isDoughnutChart() || $this->Chart->isPolarAreaChart()) {
            $this->Chart->setParameter("dataset.circular", true);
        }

        // Init X/Y Axes
        $yAxes = [];
        $x = [];
        $y = [];
        $responsive = IsMobile() && self::$DefaultResponsive;
        $scale = $this->Chart->getParameters("scale"); // Default bar chart scale

        // Set up beginAtZero/min/max (Skip for Pie/Doughnut/Polar Area/Radar)
        $vscale = [];
        if (!$this->Chart->isPieChart() && !$this->Chart->isDoughnutChart() && !$this->Chart->isPolarAreaChart() && !$this->Chart->isRadarChart()) {
            $vscale["beginAtZero"] = $this->Chart->ScaleBeginWithZero;
            if ($this->Chart->MinimumValue !== null) {
                $vscale["min"] = $this->Chart->MinimumValue;
            }
            if ($this->Chart->MaximumValue !== null) {
                $vscale["max"] = $this->Chart->MaximumValue;
            }
        }
        if (is_array($chartData)) {
            // Multi series
            if ($multiSeries == 1) {
                $labels = [];
                $datasets = [];

                // Multi-Y values
                if ($seriesType == "1") {
                    // Set up labels
                    $cntCat = count($chartData);
                    for ($i = 0; $i < $cntCat; $i++) {
                        $name = $this->Chart->formatName($chartData[$i][0]);
                        $labels[] = $name;
                    }

                    // Set up datasets
                    $cntData = count($chartData);
                    $cntSeries = count($chartSeries);
                    if ($cntData > 0 && is_array($chartData[0]) && $cntSeries > count($chartData[0]) - 2) {
                        $cntSeries = count($chartData[0]) - 2;
                    }
                    for ($i = 0; $i < $cntSeries; $i++) {
                        $seriesName = (is_array($chartSeries[$i])) ? $chartSeries[$i][0] : $chartSeries[$i];
                        $yAxisId = (is_array($chartSeries[$i])) ? $chartSeries[$i][1] : "";
                        if (!IsEmpty($yAxisId) && !in_array($yAxisId, array_column($yAxes, "id"))) { // Dual axis
                            $yAxes[$yAxisId] = ["type" => "linear", "display" => "auto", "position" => $yAxisId == "y" ? "left" : "right"];
                            if ($yAxisId != "y") {
                                $yAxes[$yAxisId]["grid"] = ["drawOnChartArea" => false];
                            }
                        }
                        $backgroundColor = $this->Chart->getRgbaBackgroundColor($i);
                        $borderColor = $this->Chart->getRgbaBorderColor($i);
                        $renderAs = $this->Chart->getRenderAs($i);
                        $showSeries = Config("CHART_SHOW_BLANK_SERIES");
                        $data = [];
                        $links = [];
                        for ($j = 0; $j < $cntData; $j++) {
                            $val = $chartData[$j][$i + 2];
                            $val = ($val === null) ? 0 : (float)$val;
                            if ($val != 0) {
                                $showSeries = true;
                            }
                            $lnk = $this->getChartLink($this->Chart->DrillDownUrl, $this->Chart->Data[$j]);
                            $links[] = $lnk;
                            $data[] = $val;
                        }
                        if ($showSeries) {
                            $dataset = $this->getDataset($data, $backgroundColor, $borderColor, $links, $seriesName, $renderAs, $yAxisId);
                            $datasets[] = $dataset;
                        }
                    }

                // Series field
                } else {
                    // Get series names
                    if (is_array($chartSeries)) {
                        $cntSeries = count($chartSeries);
                    } else {
                        $cntSeries = 0;
                    }

                    // Set up labels
                    $cntData = count($chartData);
                    for ($i = 0; $i < $cntData; $i++) {
                        $name = $chartData[$i][0];
                        if (!in_array($name, $labels)) {
                            $labels[] = $name;
                        }
                    }

                    // Set up dataset
                    $cntLabels = count($labels);
                    $cntData = count($chartData);
                    for ($i = 0; $i < $cntSeries; $i++) {
                        $seriesName = (is_array($chartSeries[$i])) ? $chartSeries[$i][0] : $chartSeries[$i];
                        $yAxisId = (is_array($chartSeries[$i])) ? $chartSeries[$i][1] : "";
                        if (!IsEmpty($yAxisId) && !in_array($yAxisId, array_column($yAxes, "id"))) { // Dual axis
                            $yAxes[$yAxisId] = ["type" => "linear", "display" => true, "position" => $yAxisId == "y" ? "left" : "right"];
                            if ($yAxisId != "y") {
                                $yAxes[$yAxisId]["grid"] = ["drawOnChartArea" => false];
                            }
                        }
                        $backgroundColor = $this->Chart->getRgbaBackgroundColor($i);
                        $borderColor = $this->Chart->getRgbaBorderColor($i);
                        $renderAs = $this->Chart->getRenderAs($i);
                        $showSeries = Config("CHART_SHOW_BLANK_SERIES");
                        $data = [];
                        $links = [];
                        for ($j = 0; $j < $cntLabels; $j++) {
                            $val = Config("CHART_SHOW_MISSING_SERIES_VALUES_AS_ZERO") ? 0 : null;
                            $lnk = "";
                            for ($k = 0; $k < $cntData; $k++) {
                                if ($chartData[$k][0] == $labels[$j] && $chartData[$k][1] == $seriesName) {
                                    $val = $chartData[$k][2];
                                    $val = ($val === null) ? 0 : (float)$val;
                                    if ($val != 0) {
                                        $showSeries = true;
                                    }
                                    $lnk = $this->getChartLink($this->Chart->DrillDownUrl, $this->Chart->Data[$k]);
                                    $links[] = $lnk;
                                    break;
                                }
                            }
                            $data[] = $val;
                        }
                        if ($showSeries) {
                            $dataset = $this->getDataset($data, $backgroundColor, $borderColor, $links, $seriesName, $renderAs, $yAxisId);
                            $datasets[] = $dataset;
                        }
                    }
                }

                // Set up Data/Options
                $this->Data = ["labels" => $labels, "datasets" => $datasets];
                $this->Options->import(["responsive" => $responsive, "maintainAspectRatio" => self::$DefaultMaintainAspectRatio, "plugins" => ["legend" => ["display" => true], "title" => ["display" => true, "text" => $title]]]);
                // Set up tooltips for stacked charts
                if ($this->Chart->isStackedChart()) {
                    $this->Options["interaction"] = ["mode" => "index"];
                }

                // Set up X/Y Axes
                if ($this->Chart->isCombinationChart()) {
                    if (count($scale) > 0) {
                        $x = $scale;
                    }
                    if (count($vscale) > 0) {
                        $y = $vscale;
                    }
                } else {
                    $stack = $this->Chart->isStackedChart() ? ["stacked" => true] : [];
                    $arx = $stack;
                    $ary = $stack;
                    if ($this->Chart->isBarChart()) {
                        $arx = array_replace_recursive($vscale, $arx);
                        $ary = array_replace_recursive($scale, $ary);
                    } else {
                        $arx = array_replace_recursive($scale, $arx);
                        $ary = array_replace_recursive($vscale, $ary);
                    }
                    if (count($arx) > 0) {
                        $x = $arx;
                    }
                    if (count($ary) > 0) {
                        $y = $ary;
                    }
                }

            // Single series
            } else {
                $cntData = count($chartData);
                $labels = [];
                $backgroundColor = [];
                $borderColor = [];
                $data = [];
                $links = [];
                for ($i = 0; $i < $cntData; $i++) {
                    $name = $this->Chart->formatName($chartData[$i][0]);
                    $bgColor = $this->Chart->getRgbaBackgroundColor($i);
                    $bdColor = $this->Chart->getRgbaBorderColor($i);
                    if ($chartData[$i][1] != "") {
                        $name .= ", " . $chartData[$i][1];
                    }
                    $val = $chartData[$i][2];
                    //$val = ($val === null) ? 0 : (float)$val;
                    $val = ($val === null) ? $val : (float)$val; // Show null value as null
                    $lnk = $this->getChartLink($this->Chart->DrillDownUrl, $this->Chart->Data[$i]);
                    $links[] = $lnk;
                    $labels[] = $name;
                    $backgroundColor[] = $bgColor;
                    $borderColor[] = $bdColor;
                    $data[] = $val;
                }

                // Set bar defaults
                if ($this->Chart->isBarChart()) {
                    if (count($scale) > 0) {
                        $y = $scale;
                    }
                    if (count($vscale) > 0) {
                        $x = $vscale;
                    }
                } else {
                    if (count($scale) > 0) {
                        $x = $scale;
                    }
                    if (count($vscale) > 0) {
                        $y = $vscale;
                    }
                }

                // Line/Area chart, use first color
                if ($this->Chart->isLineChart() || $this->Chart->isAreaChart()) {
                    $backgroundColor = $this->Chart->getRgbaBackgroundColor(0); // Use first color
                    $borderColor = $this->Chart->getRgbaBorderColor(0); // Use first color
                }

                // Get dataset
                $datasets = $cntData > 0 ? [$this->getDataset($data, $backgroundColor, $borderColor, $links)] : [];

                // Set up Data/Options
                $this->Data = ["labels" => $labels, "datasets" => $datasets];
                $showLegend = $this->Chart->isPieChart() || $this->Chart->isDoughnutChart() || $this->Chart->isPolarAreaChart() ? true : false;
                $this->Options->import(["responsive" => $responsive, "maintainAspectRatio" => self::$DefaultMaintainAspectRatio, "plugins" => ["legend" => ["display" => $showLegend], "title" => ["display" => true, "text" => $title]]]);
            }

            // Set up indexAxis = y for horizontal bar charts
            if ($this->Chart->isBarChart()) {
                $this->Options["indexAxis"] = "y";
            }

            // Set X / Y Axes
            $scales = [];
            if (count($x) > 0) {
                $scales = ["x" => $x];
            }
            if (count($y) > 0) {
                $scales = ["y" => $y];
            }
            if (count($yAxes) > 0) {
                $scales = array_merge_recursive($scales, $yAxes);
            }

            // Skip axis for Pie/Doughnut/Polar Area/Radar
            if (!$this->Chart->isPieChart() && !$this->Chart->isDoughnutChart() && !$this->Chart->isPolarAreaChart() && !$this->Chart->isRadarChart()) {
                $this->Options->import(["scales" => $scales]);
            }

            // Remove R axis if not Radar chart
            if (!$this->Chart->isRadarChart()) {
                $this->Options->remove("scales.r");
            }

            // Set showLabelBackdrop to false for Polar Area / Radar chart
            if ($this->Chart->isPolarAreaChart() || $this->Chart->isRadarChart()) {
                $this->Options["scales.r.ticks.showLabelBackdrop"] = false;
            }

            // Set up trend lines (Skip for Pie/Doughnut/Polar Area/Radar)
            $annotations = $this->getAnnotations();
            if (is_array($annotations) && !$this->Chart->isPieChart() && !$this->Chart->isDoughnutChart() && !$this->Chart->isPolarAreaChart() && !$this->Chart->isRadarChart()) {
                $this->Options->import(["plugins" => ["annotation" => $annotations]]);
            }
        }

        // Chart_Rendered event
        if (method_exists($this->Chart, "chartRendered")) {
            $this->Chart->chartRendered($this);
        }
    }

    // Get annotations
    protected function getAnnotations(): ?array
    {
        if (is_array($this->Chart->Trends)) {
            $ar = [];
            foreach ($this->Chart->Trends as $i => $trend) {
                $ar["line" . ($i + 1)] = $this->getAnnotation($trend);
            }
            return ["annotations" => $ar];
        }
        return null;
    }

    // Get annotation
    protected function getAnnotation(array $line): array
    {
        $line["type"] = "line"; // Line annotation
        $line["borderColor"] = $this->Chart->getRgbaColor($line["borderColor"], $this->Chart->getOpacity(@$line["alpha"])); // Color
        $line["endValue"] ??= $line["value"]; // End value
        $label = $line["label"];
        $display = $label ? true : false;
        $line["label"] = [
            "content" => $label,
            "backgroundColor" => $line["borderColor"],
            "display" => $display,
            "enabled" => true,
            "position" => IsRTL() ? "left" : "right"
        ];
        $line["scaleID"] = ($this->Chart->isBarChart() ? "x" : "y") . (@$line["parentYAxis"] == "S" ? "1" : ""); // Axis type + Secondary/Primary axis ID
        return $line;
    }

    // Get chart link
    protected function getChartLink(string $src, mixed $row): ?array
    {
        if ($src != "" && is_array($row)) {
            $cntrow = count($row);
            $lnk = $src;
            $sdt = $this->Chart->SeriesDateType;
            $xdt = $this->Chart->XAxisDateFormat;
            if (preg_match("/&t=([^&]+)&/", $lnk, $m)) {
                $tblCaption = Language()->tablePhrase($m[1], "TblCaption");
            } else {
                $tblCaption = "";
            }
            for ($i = 0; $i < $cntrow; $i++) { // Link format: %i:Parameter:FieldType%
                if (preg_match("/%" . $i . ":([^%:]*):([\d]+)%/", $lnk, $m)) {
                    $fldtype = FieldDataType($m[2]);
                    if ($i == 0) { // Format X SQL
                        $lnk = str_replace($m[0], Encrypt($this->Chart->getXSql("@" . $m[1], $fldtype, $row[$i], $xdt)), $lnk);
                    } elseif ($i == 1) { // Format Series SQL
                        $lnk = str_replace($m[0], Encrypt($this->Chart->getSeriesSql("@" . $m[1], $fldtype, $row[$i], $sdt)), $lnk);
                    } else {
                        $lnk = str_replace($m[0], Encrypt("@" . $m[1] . " = " . QuotedValue($row[$i], $fldtype, $this->Chart->Table->Dbid)), $lnk);
                    }
                }
            }
            return ["url" => $lnk, "id" => $this->Chart->ID, "hdr" => $tblCaption];
        }
        return null;
    }

    protected function getDataset(array $data, string|array $backgroundColor, string|array $borderColor, array $links, ?string $seriesName = null, string $renderAs = "", string $yAxisId = ""): array
    {
        $dataset = $this->Chart->getParameters("dataset"); // Load default dataset options
        $dataset["data"] = $data; // Load data
        $dataset["backgroundColor"] = $backgroundColor; // Background color
        $changeAlpha = fn($c) => preg_replace('/[\d\.]+(?=\))/', "1.0", $c); // Change alpha to 1.0
        if (is_array($backgroundColor) && is_array($borderColor) && count($backgroundColor) == count($borderColor)) {
            foreach ($borderColor as $i => &$color) {
                if ($color == $backgroundColor[$i]) {
                    $color = $changeAlpha($color);
                }
            }
        } elseif (is_string($backgroundColor) && is_string($borderColor) && $backgroundColor == $borderColor) {
            $borderColor = $changeAlpha($borderColor);
        }
        $dataset["borderColor"] = $borderColor;
        $dataset["borderWidth"] = self::$DefaultBorderWidth;
        $hasLink = count(array_filter($links)) > 0;
        $dataset["links"] = $hasLink ? $links : null; // Drill down link
        if ($seriesName !== null) { // Multi series
            $dataset["label"] = $seriesName;
            if ($this->Chart->isCombinationChart()) { // Combination chart, set render type / stack id / axis id
                $renderType = $this->getRenderType($renderAs);
                $dataset["type"] = $renderType;
                if ($renderType == "bar" && $this->Chart->isStackedChart()) { // Set up stack id
                    $dataset["stack"] = $this->Chart->ID;
                }
                if ($this->Chart->isDualAxisChart()) { // Set up axis id
                    $dataset["yAxisID"] = $yAxisId;
                }
            } elseif ($this->Chart->isStackedChart()) { // Stacked chart, set up stack id
                $dataset["stack"] = $this->Chart->ID;
            }
        }
        if ($this->Chart->isAreaChart() || $this->Chart->isCombinationChart() && SameText($renderAs, "area")) { // Area chart, set fill
            $dataset["fill"] = true;
        }
        return $dataset;
    }

    // Get render type for combination chart
    protected function getRenderType(string $renderAs): string
    {
        if (SameText($renderAs, "column")) {
            return "bar";
        } elseif (SameText($renderAs, "line") || SameText($renderAs, "area") && !$this->Chart->isStackedChart()) {
            return "line";
        } else { // Default
            return "bar";
        }
    }
}
