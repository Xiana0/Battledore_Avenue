<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\LogicException;

class SafeEntityNormalizer implements NormalizerInterface
{

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Entity;
    }

    public function getSupportedTypes(?string $format): array
    {
        // Return supported class names as keys with "true" or "false" for cacheable
        return [
            Entity::class => true,
        ];
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        // Custom normalization only for Entity class
        if (!$data instanceof Entity) {
            throw new LogicException('SafeEntityNormalizer only supports instances of Entity');
        }
        $metadata = $data->metaData();
        $output = [];
        foreach ($metadata->getFieldNames() as $field) {
            $mapping = $metadata->getFieldMapping($field);

            // Skip uninitialized fields
            if (!$data->isInitialized($field)) {
                continue;
            }

            // Skip fields marked as binary/blob
            if (!empty($mapping->options['blob'])) {
                continue;
            }
            try {
                $value = $data->$field;
            } catch (\Throwable) {
                continue; // Skip problematic fields
            }

            // Skip resources
            if (is_resource($value)) {
                continue;
            }
            $output[$field] = $value;
        }
        return $output;
    }
}
