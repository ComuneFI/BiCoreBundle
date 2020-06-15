<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Cdf\BiCoreBundle\Utils\String\StringUtils;
use function count;

class ModelUtils
{

    /*public function getClassNameToShortcutNotations($entity)
    {
        $cleanClassName = str_replace('\\Entity', '\:', $entity);
        $parts = explode('\\', $cleanClassName);

        return implode('', $parts);
    }*/

    public function getEntityColumns($entity)
    {
/*
        "fieldName" => "id"
        "type" => "integer"
        "scale" => 0
        "length" => null
        "unique" => false
        "nullable" => false
        "precision" => 0
        "id" => true
        "columnName" => "id"
        "inherited" => "App\Entity\BaseCliente"
        "declared" => "App\Entity\BaseCliente"
        
        "event_id" => "int"
        "id" => "int"
        "policy_id" => "int"
        "amount" => "double"
        "damage" => "\Swagger\Insurance\Model\ModelsDamage"
        "date_open" => "string"
        "note" => "string"
        "number" => "string"
        "paymentdate" => "string"
        "requiredamount" => "double"
        "status" => "\Swagger\Insurance\Model\ModelsStatus"
        */

        $myInstance = new $entity();
        $fieldMappings = $myInstance::swaggerTypes();

        //dump($fieldMappings);
        //dump($entity);
        $colonne = array();
        foreach ($fieldMappings as $fieldName=>$fieldType) {
            if ( \str_contains( $fieldType ,'Swagger') ) {
                //dump( $fieldType);
            }
            else {
                //dump($fieldName);
                //dump($fieldType);            
                $colonne[$fieldName]['fieldName'] = $fieldName;
                $colonne[$fieldName]['type'] = $fieldType;
                $colonne[$fieldName]['entityClass'] = $entity;
                $colonne[$fieldName]['columnName'] = $fieldName;
                if ($fieldName == 'id') {
                    $colonne[$fieldName]['id'] = true;
                }
                else {
                    $colonne[$fieldName]['id'] = false;
                }
            }
        }
        //dump($colonne);
        //exit;
        //FOLLOWING LINES FOR RELATED TABLES
       /* $joinTables = $this->getEntityJoinTables($entity);
        foreach ($joinTables as $entityjoin => $entityproperties) {
            $key = $entityproperties['entity']['fieldName'];
            $colonne[$key]['fieldName'] = $key;
            $colonne[$key]['columnName'] = $key;
            $colonne[$key]['entityClass'] = $entityjoin;
            $colonne[$key]['sourceEntityClass'] = $entity;
            $colonne[$key]['association'] = true;
            $colonne[$key]['associationtable'] = $entityproperties['entity'];
        }*/

        return $colonne;
    }
/*
    public function getTableFromEntity($entity)
    {
        $metadata = $this->em->getClassMetadata($entity);
        $tablename = $metadata->getTablename();

        return $tablename;
    }

    public function entityHasJoinTables($entityclass)
    {
        $jointables = $this->getEntityJoinTables($entityclass);

        return count($jointables) > 0 ? true : false;
    }

    public function getJoinTableField($entityclass, $field)
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

    public function getJoinTableFieldProperty($entityclass, $field)
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

    public function getEntityProperties($fieldname, $objrecord)
    {
        $parametri = array('str' => $fieldname, 'primamaiuscola' => true);
        $getfieldname = StringUtils::toCamelCase($parametri);
        if (!method_exists($objrecord, $getfieldname)) {
            $getfieldname = 'has'.StringUtils::toCamelCase($parametri);
            if (!method_exists($objrecord, $getfieldname)) {
                $getfieldname = 'is'.StringUtils::toCamelCase($parametri);
                if (!method_exists($objrecord, $getfieldname)) {
                    $getfieldname = 'get'.StringUtils::toCamelCase($parametri);
                }
            }
        }
        $setfieldname = 'set'.StringUtils::toCamelCase($parametri);

        return array('get' => $getfieldname, 'set' => $setfieldname);
    }

    public function entityExists($className)
    {
        if (is_object($className)) {
            $className = ($className instanceof Proxy) ? get_parent_class($className) : get_class($className);
        }

        return !$this->em->getMetadataFactory()->isTransient($className);
    }

    public function getEntityJoinTables($entityclass)
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

    private function getJoinFieldName($joinfield, $field)
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

    private function getJoinTable($joinfield, $field)
    {
        $joinTableEntity = $joinfield['entity'];
        $joinColumns = $joinTableEntity['joinColumns'];
        foreach ($joinColumns as $joinColumn) {
            if ($field === $joinColumn['name']) {
                return $joinTableEntity['targetEntity'];
            }
        }

        return null;
    }*/
}
