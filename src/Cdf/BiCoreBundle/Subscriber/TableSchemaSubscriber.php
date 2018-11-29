<?php

namespace Cdf\BiCoreBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Id\SequenceGenerator;

class TableSchemaSubscriber implements \Doctrine\Common\EventSubscriber
{
    public $container;
    
    public function getSubscribedEvents()
    {
        return array('loadClassMetadata');
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $tableschema = $this->container->getParameter('bi_core.table_schema');
        if ($tableschema != '') {
            $classMetadata = $args->getClassMetadata();

            $classMetadata->setPrimaryTable(array('name' => $tableschema.'.'.$classMetadata->getTableName()));
            foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
                $jointablename = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && isset($jointablename)) {
                    $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                    $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $tableschema.'.'.$mappedTableName;
                }
            }
            if ($classMetadata->isIdGeneratorSequence()) {
                $newDefinition = $classMetadata->sequenceGeneratorDefinition;
                $newDefinition['sequenceName'] = $tableschema.'.'.$newDefinition['sequenceName'];

                $classMetadata->setSequenceGeneratorDefinition($newDefinition);
                $em = $args->getEntityManager();
                if (isset($classMetadata->idGenerator)) {
                    $sequncename = $em->getConfiguration()->getQuoteStrategy()
                            ->getSequenceName($newDefinition, $classMetadata, $em->getConnection()->getDatabasePlatform());
                    $allocationSize = $newDefinition['allocationSize'];
                    $sequenceGenerator = new SequenceGenerator($sequncename, $allocationSize);
                    $classMetadata->setIdGenerator($sequenceGenerator);
                }
            }
        }
    }
}
