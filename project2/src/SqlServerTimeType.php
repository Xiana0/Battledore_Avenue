<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TimeType;
use DateTimeInterface;
use DateTime;

class SqlServerTimeType extends TimeType
{

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DateTime
    {
        if ($value === null || $value instanceof DateTimeInterface) {
            return $value;
        }

        // Trim microseconds if present
        $value = preg_replace('/\.\d+$/', '', $value);
        return DateTime::createFromFormat('H:i:s', $value) ?: null;
    }
}
