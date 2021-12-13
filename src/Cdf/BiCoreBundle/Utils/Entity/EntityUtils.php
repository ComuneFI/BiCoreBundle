<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Cdf\BiCoreBundle\Utils\String\StringUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Proxy\Proxy;
use function count;

class EntityUtils
{

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getClassNameToShortcutNotations(string $entity): string
    {
        $cleanClassName = str_replace('\\Entity', '\:', $entity);
        $parts = explode('\\', $cleanClassName);

        return implode('', $parts);
    }

    /**
     *
     * @param string $entity
     * @return array<mixed>
     */
    public function getEntityColumns(string $entity): array
    {
        $infocolonne = $this->em->getMetadataFactory()->getMetadataFor($entity);
        $colonne = array();
        $fieldMappings = $infocolonne->fieldMappings;
        //dump($fieldMappings);
        foreach ($fieldMappings as $colonna) {
            $colonne[$colonna['fieldName']] = $colonna;
            $colonne[$colonna['fieldName']]['entityClass'] = $entity;
        }
        $joinTables = $this->getEntityJoinTables($entity);
        foreach ($joinTables as $entityjoin => $entityproperties) {
            $key = $entityproperties['entity']['fieldName'];
            $colonne[$key]['fieldName'] = $key;
            $colonne[$key]['columnName'] = $key;
            $colonne[$key]['entityClass'] = $entityjoin;
            $colonne[$key]['sourceEntityClass'] = $entity;
            $colonne[$key]['association'] = true;
            $colonne[$key]['associationtable'] = $entityproperties['entity'];
        }
        return $colonne;
    }

    public function getTableFromEntity(string $entity): string
    {
        $metadata = $this->em->getClassMetadata($entity);
        $tablename = $metadata->getTablename();

        return $tablename;
    }

    public function entityHasJoinTables(string $entityclass): bool
    {
        $jointables = $this->getEntityJoinTables($entityclass);

        return count($jointables) > 0 ? true : false;
    }

    public function getJoinTableField(string $entityclass, string $field): ?string
    {
        $joinfields = $this->getEntityJoinTables($entityclass);
        foreach ($joinfields as $joinfield) {
            if (1 != count($joinfield)) {
                return null;
            }
            $jointableentity = $this->getJoinTable($joinfield, $field);
            if ($jointableentity) {
                return $jointableentity;
            }
        }

        return null;
    }

    public function getJoinTableFieldProperty(string $entityclass, string $field): ?string
    {
        $joinfields = $this->getEntityJoinTables($entityclass);
        foreach ($joinfields as $joinfield) {
            if (1 != count($joinfield)) {
                return null;
            }
            $joinfieldname = $this->getJoinFieldName($joinfield, $field);
            if ($joinfieldname) {
                return $joinfieldname;
            }
        }

        return null;
    }

    /**
     *
     * @param string $fieldname
     * @param mixed $objrecord
     * @return array<mixed>
     */
    public function getEntityProperties(string $fieldname, $objrecord): array
    {
        $parametri = array('str' => $fieldname, 'primamaiuscola' => true);
        $getfieldname = StringUtils::toCamelCase($parametri);
        if (!method_exists($objrecord, $getfieldname)) {
            $getfieldname = 'has' . StringUtils::toCamelCase($parametri);
            if (!method_exists($objrecord, $getfieldname)) {
                $getfieldname = 'is' . StringUtils::toCamelCase($parametri);
                if (!method_exists($objrecord, $getfieldname)) {
                    $getfieldname = 'get' . StringUtils::toCamelCase($parametri);
                }
            }
        }
        $setfieldname = 'set' . StringUtils::toCamelCase($parametri);

        return array('get' => $getfieldname, 'set' => $setfieldname);
    }

    /**
     *
     * @param mixed $className
     * @return bool
     */
    public function entityExists($className): bool
    {
        if (is_object($className)) {
            $className = ($className instanceof Proxy) ? get_parent_class($className) : get_class($className);
        }

        return !$this->em->getMetadataFactory()->isTransient($className);
    }

    /**
     *
     * @param string $entityclass
     * @return array<mixed>
     */
    public function getEntityJoinTables(string $entityclass): array
    {
        $jointables = array();
        $metadata = $this->em->getClassMetadata($entityclass);
        $fielsassoc = $metadata->associationMappings;
        foreach ($fielsassoc as $tableassoc) {
            if ($tableassoc['inversedBy']) {
                $jointables[$tableassoc['targetEntity']] = array('entity' => $tableassoc);
            }
        }

        return $jointables;
    }

    /**
     *
     * @param array<mixed> $joinfield
     * @param string $field
     * @return string|null
     */
    private function getJoinFieldName(array $joinfield, string $field): ?string
    {
        $joinFieldentity = $joinfield['entity'];
        $joinColumns = $joinFieldentity['joinColumns'];
        foreach ($joinColumns as $joinColumn) {
            if ($field === $joinColumn['name']) {
                $joinFieldName = $joinFieldentity['fieldName'];

                return $joinFieldName;
            }
        }

        return null;
    }

    /**
     *
     * @param array<mixed> $joinfield
     * @param string $field
     * @return string
     */
    private function getJoinTable(array $joinfield, string $field): ?string
    {
        $joinTableEntity = $joinfield['entity'];
        $joinColumns = $joinTableEntity['joinColumns'];
        foreach ($joinColumns as $joinColumn) {
            if ($field === $joinColumn['name']) {
                return $joinTableEntity['targetEntity'];
            }
        }

        return null;
    }
}
