<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Exception;

/**
 * Chart exporter
 */
class ChartExporter
{
    // Constructor
    public function __construct(
        protected RequestStack $requestStack,
        protected Language $language,
    ) {
    }

    // Export
    public function __invoke(): Response
    {
        try {
            $json = $this->requestStack->getCurrentRequest()->request->get("charts", "[]");
            $charts = json_decode($json);
            $files = [];
            foreach ($charts as $chart) {
                $img = false;

                // Chart is base64 string
                if ($chart->streamType == "base64") {
                    $img = base64_decode(preg_replace('/^data:image\/\w+;base64,/', "", $chart->stream));
                }
                if ($img === false) {
                    throw new Exception(sprintf($this->language->phrase("ChartExportError1"), $chart->streamType, $chart->chartEngine));
                }

                // Save the file
                $filename = $chart->fileName;
                if ($filename == "") {
                    throw new Exception($this->language->phrase("ChartExportError2"));
                }
                $path = UploadTempPath();
                if (!CreateDirectory($path)) {
                    throw new Exception($this->language->phrase("ChartExportError3"));
                }
                if (!is_writable(PrefixDirectoryPath($path))) {
                    throw new Exception($this->language->phrase("ChartExportError4"));
                }
                $filepath = IncludeTrailingDelimiter($path, false) . $filename;
                WriteFile($filepath, $img);
                $files[] = $filename;
            }

            // Return success response
            return new JsonResponse(["success" => true, "files" => $files]);
        } catch (Throwable $e) {
            return new JsonResponse(["success" => false, "error" => $e->getMessage()]);
        }
    }
}
