<?php

/**
 * This is an utility class in order to remove complex computations from ApiManager.
 */


namespace Cdf\BiCoreBundle\Utils\Api;

use \DateTime;

class ApiManagerUtil
{

    /**
     * Map first object transformed into the second where possible,
     * attempting to map each field of first into field of the second.
     */
    public function mapData(mixed $modelEntity, mixed $controllerItem): mixed
    {
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
     * Return value of string when format type is datetime, otherwise null
     */
    private function castDateTime(mixed $oldvalue, ?string $formatType): ?DateTime
    {
        $value = null;
        if ($formatType == 'datetime') {
            if (!empty($oldvalue)) {
                $oldvalue = str_replace('/', '-', $oldvalue);
                $time = strtotime($oldvalue);
                $value = new DateTime();
                if ($time != false) {
                    $value->setTimestamp($time);
                }
            }
        }
        return $value;
    }


    /**
     * Try to insert in automatic way the conversion to a BiCore known value
     */
    public function getValueOfData(?string $fieldType, ?string $formatType, ?string $oldvalue): mixed
    {
        $value = $oldvalue;

        switch ($fieldType) {
            case null:
                break;
            case 'int':
                $value = (int)$value;
                break;
            case 'double':
                $value = (double)$value;
                break;
            case 'bool':
                $value = (bool)$value;
                break;
            case 'string':
                $value = $this->castDateTime($formatType, $oldvalue);
                break;
        }
        return $value;
    }

    /**
     * It prepares entity values so that they can be used with types compliant with BiCoreBundle.
     * For example it transforms a date that arrive in string format into a DateTime.
     */
    public function setupApiValues(mixed $entityout): mixed
    {
        $fieldMappings = $entityout::swaggerTypes();
        $formatMappings = $entityout::swaggerFormats();
        $setters = $entityout::setters();
        $getters = $entityout::getters();

        foreach ($fieldMappings as $fieldName => $fieldType) {
                $setvalue = $setters[$fieldName];
                $getvalue = $getters[$fieldName];
                $newvalue = $this->getValueOfData($fieldType, $formatMappings[$fieldName], $entityout->$getvalue());
                $entityout->$setvalue($newvalue);
        }
        return $entityout;
    }
}
