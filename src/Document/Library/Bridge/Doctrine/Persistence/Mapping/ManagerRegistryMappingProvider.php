<?php

namespace Zenstruck\Document\Library\Bridge\Doctrine\Persistence\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Document\Attribute\Mapping;
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

            $config[$mapping['fieldName']] = Mapping::fromProperty(
                $metadata->getReflectionProperty($mapping['fieldName']),
                $mapping['options'] ?? [],
            );
        }

        // configure virtual documents
        foreach ($metadata->reflClass->getProperties() as $property) {
            if (isset($config[$property->name])) {
                // already configured
                continue;
            }

            if ($attribute = $property->getAttributes(Mapping::class)[0] ?? null) {
                $config[$property->name] = $attribute->newInstance();
                $config[$property->name]->extra['_virtual'] = true;
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
