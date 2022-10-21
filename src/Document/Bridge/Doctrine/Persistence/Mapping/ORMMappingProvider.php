<?php

namespace Zenstruck\Document\Bridge\Doctrine\Persistence\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Annotation\Context;
use Zenstruck\Document\Bridge\Doctrine\DBAL\Types\DocumentType;
use Zenstruck\Document\Bridge\Doctrine\Persistence\MappingProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ORMMappingProvider implements MappingProvider
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    public function get(string $class): array
    {
        // todo caching, warmup
        $metadata = $this->registry->getManagerForClass($class)?->getClassMetadata($class);

        if (!$metadata instanceof ClassMetadata) {
            throw new \LogicException(); // todo
        }

        $config = [];

        foreach ($metadata->fieldMappings as $mapping) {
            // todo embedded
            if (DocumentType::NAME !== $mapping['type']) {
                continue;
            }

            $options = \array_merge(
                self::configFromAttribute($metadata->getReflectionProperty($mapping['fieldName'])),
                $mapping['options'] ?? [],
            );

            if (!isset($options['library'])) {
                throw new \LogicException('library not configured'); // todo
            }

            $config[$mapping['fieldName']] = $options;
        }

        return $config;
    }

    private static function configFromAttribute(\ReflectionProperty $property): array
    {
        if (!\class_exists(Context::class)) {
            return [];
        }

        if (!$context = ($property->getAttributes(Context::class)[0] ?? null)?->newInstance()) {
            return [];
        }

        return $context->getContext();
    }
}
