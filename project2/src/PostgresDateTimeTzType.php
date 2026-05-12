<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeTzType;
use DateTime;
use DateTimeInterface;

/**
 * Custom Doctrine type for PostgreSQL timestamptz columns.
 *
 * PostgreSQL returns timestamp with time zone values in ISO 8601 format, e.g.:
 *   "2026-03-16 08:03:05.645439+00"
 *
 * Doctrine's DateTimeTzType expects the format "Y-m-d H:i:sO", e.g.:
 *   "2026-03-16 08:03:05+0000"
 *
 * Microseconds are preserved in both directions so no precision is lost
 * on read or write.
 */
class PostgresDateTimeTzType extends DateTimeTzType
{
    /**
     * Normalize the PostgreSQL timezone offset to the 4-digit no-colon format
     * Doctrine's DateTimeTzType expects, then parse preserving microseconds.
     *
     * Handles all PostgreSQL offset variants in a single pass:
     *   "+00"    (2-digit UTC shorthand)  -> "+0000"
     *   "+05:30" (colon-separated)        -> "+0530"
     *   "+0530"  (already correct)        -> unchanged
     *   "+0000"  (already correct)        -> unchanged
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DateTime
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof DateTime) {
            return $value;
        }

        // Normalize the timezone offset in a single pass.
        // Matches "+HH" (no minutes) and "+HH:MM" (colon-separated) at end of string.
        // "+HHMM" (already correct) is left unchanged.
        $normalized = preg_replace_callback(
            '/([+-]\d{2})(?::(\d{2}))?$/',
            static fn($m) => $m[1] . ($m[2] ?? '00'),
            $value
        ) ?? $value; // fall back to original value on PCRE error

        // Parse with microseconds ("Y-m-d H:i:s.uO") to preserve sub-second precision.
        // Fall back to the parent parser for values without fractional seconds.
        $dt = DateTime::createFromFormat('Y-m-d H:i:s.uO', $normalized)
            ?: parent::convertToPHPValue($normalized, $platform);
        return $dt instanceof DateTime ? $dt : null;
    }

    /**
     * Write back with microseconds to maintain round-trip precision.
     * The parent uses "Y-m-d H:i:sO" which drops sub-second digits.
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s.uO');
        }
        return parent::convertToDatabaseValue($value, $platform);
    }
}
