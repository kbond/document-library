<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Document\Library\Bridge\Doctrine\DBAL\Types\DocumentJsonType;
use Zenstruck\Document\Library\Bridge\Doctrine\DBAL\Types\DocumentStringType;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;
use Zenstruck\Document\Library\Bridge\Doctrine\Persistence\MappingProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ManagerRegistryMappingProvider implements MappingProvider
{
    private const MAPPING_TYPES = [DocumentJsonType::NAME, DocumentStringType::NAME];
    private const STRING_MAPPING_TYPES = [DocumentStringType::NAME];

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

        foreach ($metadata->fieldMappings as $field) {
            // todo embedded
            if (!\in_array($field['type'], self::MAPPING_TYPES, true)) {
                continue;
            }

            $mapping = Mapping::fromProperty(
                $metadata->getReflectionProperty($field['fieldName']),
                $field['options'] ?? [],
            );

            if ($mapping->metadata && \in_array($field['type'], self::STRING_MAPPING_TYPES, true)) {
                throw new \LogicException(\sprintf('Cannot use "%s" with metadata (%s::$%s).', $field['type'], $metadata->name, $field['fieldName']));
            }

            $config[$field['fieldName']] = $mapping;
        }

        // configure virtual documents
        foreach ($metadata->reflClass?->getProperties() ?: [] as $property) {
            if (isset($config[$property->name])) {
                // already configured
                continue;
            }

            if ($attribute = $property->getAttributes(Mapping::class)[0] ?? null) {
                $config[$property->name] = $attribute->newInstance();
                $config[$property->name]->virtual = true;
            }
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
}
