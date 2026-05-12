<?php

namespace PHPMaker2026\Project1;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Utils;
use Monolog\LogRecord;

/**
 * Stores to CSV file
 */
class AuditTrailHandler extends RotatingFileHandler
{
    public static string $Delimiter = ",";
    public static string $Enclosure = '"';
    public static string $EscapeChar = "\\";
    public static bool $UseHeader = true;
    public static array $Headers = ["date/time", "script", "user", "action", "table", "field", "key value", "old value", "new value"];
    private bool $writeHeader = true;

    /**
     * @inheritdoc
     */
    protected function streamWrite($stream, LogRecord $record): void
    {
        if (self::$UseHeader && filesize($this->url) == 0 && $this->writeHeader) {
            fputcsv($stream, self::$Headers, self::$Delimiter, self::$Enclosure, self::$EscapeChar); // Write headers
            $this->writeHeader = false; // No need to write header again (file size may be 0 due to buffering)
        }
        if (is_array($record["context"])) {
            foreach ($record["context"] as $key => $info) {
                if (is_array($info)) {
                    $record["context"][$key] = Utils::jsonEncode($info);
                }
            }
        }
        fputcsv($stream, (array)$record["context"], self::$Delimiter, self::$Enclosure, self::$EscapeChar);
    }

    /**
     * Gets the default formatter.
     *
     * Overwrite this if the LineFormatter is not a good default for your handler.
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new NormalizerFormatter();
    }
}
