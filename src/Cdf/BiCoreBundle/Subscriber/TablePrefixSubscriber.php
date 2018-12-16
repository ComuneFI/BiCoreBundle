<?php

namespace Cdf\BiCoreBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TablePrefixSubscriber implements \Doctrine\Common\EventSubscriber
{
    private $tableprefix;

    public function __construct($tableprefix)
    {
        $this->tableprefix = $tableprefix;
    }

    public function getSubscribedEvents()
    {
        return array('loadClassMetadata');
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $classMetadata = $args->getClassMetadata();
        if ($classMetadata->isInheritanceTypeSingleTable() && !$classMetadata->isRootEntity()) {
            // if we are in an inheritance hierarchy, only apply this once
            return;
        }
        if (false !== strpos($classMetadata->namespace, 'Cdf\BiCoreBundle')) {
            $tableprefix = $this->tableprefix;
            $classMetadata->setPrimaryTable(array('name' => $tableprefix.$classMetadata->getTableName()));
            foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
                if (\Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY == $mapping['type'] &&
                    isset($classMetadata->associationMappings[$fieldName]['joinTable']['name'])
                ) {
                    $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                    $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $tableprefix.$mappedTableName;
                }
            }
        }
    }
}
