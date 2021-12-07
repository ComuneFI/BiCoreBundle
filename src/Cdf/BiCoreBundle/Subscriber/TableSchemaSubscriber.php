<?php

namespace Cdf\BiCoreBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Id\SequenceGenerator;

class TableSchemaSubscriber implements \Doctrine\Common\EventSubscriber
{

    private string $schemaprefix;

    public function __construct(string $schemaprefix)
    {
        $this->schemaprefix = $schemaprefix;
    }

    public function getSubscribedEvents(): array
    {
        return array('loadClassMetadata');
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $classMetadata = $args->getClassMetadata();
        if (!$this->schemaprefix || ($classMetadata->isInheritanceTypeSingleTable() && !$classMetadata->isRootEntity())) {
            return;
        }
        $classMetadata->setPrimaryTable(array('name' => $this->schemaprefix . '.' . $classMetadata->getTableName()));
        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if (\Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY == $mapping['type'] &&
                    isset($classMetadata->associationMappings[$fieldName]['joinTable']['name'])
            ) {
                $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->schemaprefix . '.' . $mappedTableName;
            }
        }

        if ($classMetadata->isIdGeneratorSequence()) {
            $newDefinition = $classMetadata->sequenceGeneratorDefinition;
            $newDefinition['sequenceName'] = $this->schemaprefix . '.' . $newDefinition['sequenceName'];

            $classMetadata->setSequenceGeneratorDefinition($newDefinition);
            $em = $args->getEntityManager();
            /** @phpstan-ignore-next-line */
            if (isset($classMetadata->idGenerator)) {
                $sequenceGenerator = new SequenceGenerator(
                    $em->getConfiguration()->getQuoteStrategy()->getSequenceName(
                        $newDefinition,
                        $classMetadata,
                        $em->getConnection()->getDatabasePlatform()
                    ),
                    (int)$newDefinition['allocationSize']
                );
                $classMetadata->setIdGenerator($sequenceGenerator);
            }
        }
    }
}
