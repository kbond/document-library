<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Annotation\Context;
use Zenstruck\Document\Library\Bridge\Doctrine\DBAL\Types\DocumentType;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\MappingProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ManagerRegistryMappingProvider implements MappingProvider
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    public function get(string $class): array
    {
        $metadata = $this->registry->getManagerForClass($class)?->getClassMetadata($class);

        if (!$metadata instanceof ClassMetadata) {
            return []; // todo support other object managers
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

    public function all(): array
    {
        $config = [];

        foreach ($this->registry->getManagers() as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                $config[$metadata->getName()] = $this->get($metadata->getName());
            }
        }

        return \array_filter($config);
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
