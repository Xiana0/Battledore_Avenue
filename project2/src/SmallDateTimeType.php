<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;
use DateTimeInterface;

class SmallDateTimeType extends DateTimeType
{

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof DateTimeInterface) {
            // SQL Server smalldatetime: truncate to minutes
            return $value->format('Y-m-d H:i');
        }
        return parent::convertToDatabaseValue($value, $platform);
    }
}
