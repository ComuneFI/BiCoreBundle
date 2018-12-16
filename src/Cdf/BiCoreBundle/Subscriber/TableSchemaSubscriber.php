<?php

namespace Cdf\BiCoreBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Id\SequenceGenerator;

class TableSchemaSubscriber implements \Doctrine\Common\EventSubscriber
{
    private $schemaprefix;

    public function __construct($schemaprefix)
    {
        $this->schemaprefix = $schemaprefix;
    }

    public function getSubscribedEvents()
    {
        return array('loadClassMetadata');
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        $tableschema = $this->schemaprefix;
        if ('' != $tableschema) {
            $classMetadata = $args->getClassMetadata();

            $classMetadata->setPrimaryTable(array('name' => $tableschema.'.'.$classMetadata->getTableName()));
            foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
                if (isset($classMetadata->associationMappings[$fieldName]['joinTable']) && ClassMetadataInfo::MANY_TO_MANY == $mapping['type']) {
                    $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                    $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $tableschema.'.'.$mappedTableName;
                }
            }
            if ($classMetadata->isIdGeneratorSequence()) {
                $newDefinition = $classMetadata->sequenceGeneratorDefinition;
                $newDefinition['sequenceName'] = $newDefinition['sequenceName'];
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
