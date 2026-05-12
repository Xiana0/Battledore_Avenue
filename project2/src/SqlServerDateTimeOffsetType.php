<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeTzType;
use DateTime;

class SqlServerDateTimeOffsetType extends DateTimeTzType
{

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DateTime
    {
        if ($value === null) {
            return null;
        }

        // Truncate fractional seconds to 6 digits
        $normalized = preg_replace(
            '/(\.\d{6})\d*(?=\s*[+-]\d{2}:\d{2})/',
            '$1',
            $value
        );
        return parent::convertToPHPValue($normalized, $platform);
    }
}
