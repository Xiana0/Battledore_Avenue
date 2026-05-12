<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Symfony\Component\Clock\DatePoint;
use DateTimeInterface;
use DateTimeImmutable;
final class DatePointType extends DateTimeImmutableType
{
    public const NAME = 'date_point';

    /**
     * Convert database value to a DatePoint instance.
     *
     * @param mixed $value
     *
     * @return DatePoint|null
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DatePoint
    {
        if ($value === null || $value instanceof DatePoint) {
            return $value;
        }
        if ($value instanceof DateTimeInterface) {
            // If it's a mutable DateTime, convert to immutable first
            $immutable = $value instanceof DateTimeImmutable ? $value : DateTimeImmutable::createFromInterface($value);
            return DatePoint::createFromInterface($immutable);
        }

        // Let Doctrine convert it to DateTimeImmutable
        $immutable = parent::convertToPHPValue($value, $platform);
        return $immutable instanceof DateTimeImmutable
            ? DatePoint::createFromInterface($immutable)
            : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
