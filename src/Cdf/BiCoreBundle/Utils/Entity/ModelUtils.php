<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Cdf\BiCoreBundle\Utils\String\StringUtils;
use Cdf\BiCoreBundle\Utils\Api\ApiUtils;
use App\ApiModels\ModelsClaimExt;
use Swagger\Insurance\Model\ModelsEvent;
use DateTime;
use function count;

/**
 * @codeCoverageIgnore
 */
class ModelUtils
{

    /**
     * Return the array of types and formats
     */
    public function getAttributes($controllerItem): array
    {
        $myInstance = new $controllerItem();
        $fieldMappings = $myInstance::swaggerTypes();
        $formatMappings = $myInstance::swaggerFormats();
        $outcomes = array();
        foreach ($fieldMappings as $fieldName => $fieldType) {
            if (\str_contains($fieldType, 'Swagger')) {
                $fieldName .= '_enum';
                $outcomes[$fieldName]['format'] = 'int';
            } else {
                $outcomes[$fieldName]['format'] = $formatMappings[$fieldName];
            }
            $outcomes[$fieldName]['type'] = $fieldType;
        }
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
        foreach ($fieldMappings as $fieldName => $fieldType) {
            if (\str_contains($fieldName, 'NOTenum')) {
                //dump("Trovato enum ".$fieldName);
            } else {
                $colonne[$fieldName]['fieldName'] = $fieldName;
                //$colonne[$fieldName]['type'] = $formatMappings[$fieldName];
                $colonne[$fieldName]['type'] = $this->getTypeOfData($fieldType, $formatMappings[$fieldName]);
                $colonne[$fieldName]['entityClass'] = $entity;
                $colonne[$fieldName]['columnName'] = $fieldName;
                if ($fieldName == 'id') {
                    $colonne[$fieldName]['id'] = true;
                } else {
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
        if ($fieldType == 'bool') {
            $type = 'string2bool';
        } elseif ($formatType == null) {
            $type = 'string';
        } elseif ($fieldType != $formatType) {
            if ($formatType == 'datetime') {
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

        foreach ($fieldMappings as $fieldName => $fieldType) {
            /*if ( \str_contains( $fieldType ,'Swagger') ) {
                $setvalue = $setters[$fieldName];
                $getvalue = $getters[$fieldName];
            }
            else {*/
                $setvalue = $setters[$fieldName];
                $getvalue = $getters[$fieldName];
                $newvalue = $this->getValueOfData($fieldType, $formatMappings[$fieldName], $entityout->$getvalue());
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

        foreach ($setters as $setterKey => $setterMethod) {
            if (isset($getters[$setterKey])) {
                $getMethod = $getters[$setterKey];
                $controllerItem->$setterMethod($modelEntity->$getMethod());
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
        if ($formatType == null) {
        } elseif ($fieldType != $formatType) {
            if ($formatType == 'datetime' && $oldvalue != null) {
                $time = strtotime($oldvalue);
                $value = new DateTime();
                $value->setTimestamp($time);
            }
        }
        return $value;
    }
}
