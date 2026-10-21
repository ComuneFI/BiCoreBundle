<?php

namespace Cdf\BiCoreBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TablePrefixSubscriber implements \Doctrine\Common\EventSubscriber
{
    public function __construct(private readonly string $tableprefix)
    {
    }

    public function getSubscribedEvents(): array
    {
        return ['loadClassMetadata'];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $classMetadata = $args->getClassMetadata();
        if ($classMetadata->isInheritanceTypeSingleTable() && !$classMetadata->isRootEntity()) {
            // if we are in an inheritance hierarchy, only apply this once
            return;
        }
        if (str_contains($classMetadata->namespace, 'Cdf\BiCoreBundle')) {
            $tableprefix = $this->tableprefix;
            $classMetadata->setPrimaryTable(['name' => $tableprefix . $classMetadata->getTableName()]);
            foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
                if (\Doctrine\ORM\Mapping\ClassMetadata::MANY_TO_MANY == $mapping['type'] &&
                    isset($classMetadata->associationMappings[$fieldName]['joinTable']['name'])
                ) {
                    $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                    $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $tableprefix . $mappedTableName;
                }
            }
        }
    }
}
