<?php

namespace Zenstruck\Document\Bridge\Doctrine\Persistence;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Document\Bridge\Doctrine\DBAL\Types\DocumentType;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MappingConfigProvider
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    /**
     * @param class-string $class
     */
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

            $config[$mapping['fieldName']] = [
                'library' => $mapping['options']['library'] ?? throw new \LogicException(),
            ];
        }

        return $config;
    }
}
