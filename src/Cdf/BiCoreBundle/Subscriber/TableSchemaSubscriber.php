<?php

namespace Cdf\BiCoreBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

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
        $classMetadata = $args->getClassMetadata();
        if (!$this->schemaprefix || ($classMetadata->isInheritanceTypeSingleTable() && !$classMetadata->isRootEntity())) {
            return;
        }
        $classMetadata->setPrimaryTable(array('name' => $this->schemaprefix.'.'.$classMetadata->getTableName()));
        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if (\Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY == $mapping['type'] &&
                    isset($classMetadata->associationMappings[$fieldName]['joinTable']['name'])
            ) {
                $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->schemaprefix.'.'.$mappedTableName;
            }
        }

        if ($classMetadata->isIdGeneratorSequence()) {
            $newDefinition = $classMetadata->sequenceGeneratorDefinition;
            $newDefinition['sequenceName'] = $this->schemaprefix.'.'.$newDefinition['sequenceName'];

            $classMetadata->setSequenceGeneratorDefinition($newDefinition);
            $em = $args->getEntityManager();
            if (isset($classMetadata->idGenerator)) {
                $sequenceGenerator = new \Doctrine\ORM\Id\SequenceGenerator(
                    $em->getConfiguration()->getQuoteStrategy()->getSequenceName(
                        $newDefinition,
                        $classMetadata,
                        $em->getConnection()->getDatabasePlatform()
                    ),
                    $newDefinition['allocationSize']
                );
                $classMetadata->setIdGenerator($sequenceGenerator);
            }
        }
    }
}
