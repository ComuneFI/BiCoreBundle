<?php
namespace Cdf\BiCoreBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TablePrefixSubscriber implements \Doctrine\Common\EventSubscriber
{
    public $container;
    
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
            $classMetadata->setPrimaryTable(array('name' => $this->container->getParameter('bi_core.table_prefix') . $classMetadata->getTableName()));
            foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
                if ($mapping['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY &&
                    isset($classMetadata->associationMappings[$fieldName]['joinTable']['name'])
                ) {
                    $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                    $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->container->getParameter('bi_core.table_prefix') . $mappedTableName;
                }
            }
        }
    }
}
