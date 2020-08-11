<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Cdf\BiCoreBundle\Utils\String\StringUtils;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use App\ApiModels\ModelsClaimExt;
use Swagger\Insurance\Model\ModelsEvent;
use function count;

class ModelUtils
{

    /*public function getClassNameToShortcutNotations($entity)
    {
        $cleanClassName = str_replace('\\Entity', '\:', $entity);
        $parts = explode('\\', $cleanClassName);

        return implode('', $parts);
    }*/

    /**
     * Return the array of types and formats
     */
    public function getAttributes($controllerItem): array 
    {
        $myInstance = new $controllerItem();
        $fieldMappings = $myInstance::swaggerTypes();
        $formatMappings = $myInstance::swaggerFormats();
        $outcomes = array();
        foreach($fieldMappings as $fieldName=>$fieldType) {
                $outcomes[$fieldName]['type'] = $fieldType;
                $outcomes[$fieldName]['format'] = $formatMappings[$fieldName];
                if ( \str_contains( $fieldType ,'Swagger') ) {
                    $outcomes[$fieldName]['format'] = 'int';
                }
        }
        //dump("attributes");
        //dump($outcomes);
        return $outcomes;
    }

    /**
     * Return the entity columns for the grid display
     */
    public function getEntityColumns($entity)
    {
        $myInstance = new $entity();
        $fieldMappings = $myInstance::swaggerTypes();
        $formatMappings = $myInstance::swaggerFormats();

        //dump($fieldMappings);
        $colonne = array();
        foreach ($fieldMappings as $fieldName=>$fieldType) {
            if ( \str_contains( $fieldName ,'NOTenum') ) {
                //dump("Trovato enum ".$fieldName);
            }
            else {         
                $colonne[$fieldName]['fieldName'] = $fieldName;
                //$colonne[$fieldName]['type'] = $formatMappings[$fieldName];
                $colonne[$fieldName]['type'] = $this->getTypeOfData( $fieldType, $formatMappings[$fieldName] );
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
        //dump("colonne");
        return $colonne;
    }


    /**
     * Try to insert in automatic way the conversion to a BiCore known type
     */
    private function getTypeOfData($fieldType, $formatType): String 
    {
        $type = $formatType;
        if( $formatType == null) {
            $type = 'string';
        }
        else if ( $fieldType != $formatType ) {
            if ( $formatType == 'datetime' ) {
                $type = 'string2'.$type;
            }
        }
        return $type;
    }

    /**
     * It prepares entity values so that they can be used with types compliant with BiCoreBundle.
     * For example it transforms a date that arrive in string format into a DateTime.
     */
    public function setApiValues($entityout)
    {
    
        $fieldMappings = $entityout::swaggerTypes();
        $formatMappings = $entityout::swaggerFormats();
        $setters = $entityout::setters();
        $getters = $entityout::getters();

        foreach ($fieldMappings as $fieldName=>$fieldType) {
            /*if ( \str_contains( $fieldType ,'Swagger') ) {
                $setvalue = $setters[$fieldName];
                $getvalue = $getters[$fieldName];
            }
            else {*/
                $setvalue = $setters[$fieldName];
                $getvalue = $getters[$fieldName];
                $newvalue = $this->getValueOfData( $fieldType, $formatMappings[$fieldName] , $entityout->$getvalue() );
                $entityout->$setvalue($newvalue);
            /*}*/
        }
        return $entityout;
    }

    public function getControllerItem($modelEntity, $controllerItemClass) 
    {
        $controllerItem = new $controllerItemClass();
        $setters = $controllerItem::setters();
        $getters = $modelEntity::getters();

        foreach($setters as $setterKey=>$setterMethod) {
            if(isset($getters[$setterKey])) {
                $getMethod = $getters[$setterKey];
                $controllerItem->$setterMethod( $modelEntity->$getMethod() );
            }
        }

        return $controllerItem;
    }

    /**
     * Try to insert in automatic way the conversion to a BiCore known value
     */
    private function getValueOfData($fieldType, $formatType, $oldvalue)
    {
        $value = $oldvalue;
        if( $formatType == null) {
        }
        else if ( $fieldType != $formatType ) {
            if ( $formatType == 'datetime' ) {
                $time = strtotime($oldvalue);
                $value = new \DateTime();
                $value->setTimestamp($time);
            }
        }
        return $value;
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
