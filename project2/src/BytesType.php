<?php

namespace PHPMaker2026\Project1;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Bytes
 */
class BytesType extends Type
{
    const NAME = 'bytes';

    public function getName()
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return bin2hex($value ?? ''); // Convert binary data to hex string
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return hex2bin($value ?? ''); // Convert hex string to binary data
    }
}
