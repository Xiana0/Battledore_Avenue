<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;
use DateTimeInterface;

class SqlServerDateTimeType extends DateTimeType
{

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof DateTimeInterface) {
            // SQL Server DATETIME supports max 3 fractional digits
            return $value->format('Y-m-d H:i:s.v'); // .v = milliseconds
        }
        return parent::convertToDatabaseValue($value, $platform);
    }
}
