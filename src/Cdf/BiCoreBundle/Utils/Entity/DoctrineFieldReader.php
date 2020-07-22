<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

use Exception;
use Doctrine\Common\Inflector\Inflector;
use Cdf\BiCoreBundle\Utils\FieldType\FieldTypeUtils;

class DoctrineFieldReader
{
    private $tableprefix;

    public function __construct($tableprefix)
    {
        $this->tableprefix = $tableprefix;
    }

    public function getField2Object($fieldname, $object, $decodifiche = null)
    {
        $property = '';
        $field = '';
        $propertyfound = false;
        $subfields = explode('.', str_replace($this->tableprefix, '', $fieldname));
        foreach ($subfields as $field) {
            $property = $this->getObjectProperty($field, $object);
            if ($property) {
                $object = $object->$property();
                $propertyfound = true;
            }
        }
        if (!$propertyfound) {
            throw new Exception('Proprietà '.$field.' non trovata per '.$fieldname);
        }
        if ($decodifiche) {
            if (key_exists($object, $decodifiche)) {
                $object = $decodifiche[$object];
            }
        }

        return $object;
    }

    /**
     * Try to read the .env Key value provided as $key, otherwise return the default value
     */
    private function getEnvVar(String $key, String $defaultValue) {
        return (getenv($key)===false)?$defaultValue:getenv($key);
    }

    public function object2View($object, $type = null, $decodifiche = null)
    {
        $risposta = null;

        if ($decodifiche) {
            $type = 'string';
        }

        if (!is_null($object)) {
            switch (strtolower($this->getObjectType($type, $object))) {
                case 'array':
                    $risposta = print_r($object, true);
                    break;
                case 'date':
                    $risposta = $object->format(FieldTypeUtils::getEnvVar("DATE_FORMAT","d/m/Y"));
                    break;
                case 'datetime':
                    $risposta = $object->format(FieldTypeUtils::getEnvVar("DATETIME_FORMAT","d/m/Y H:i"));
                    break;
                case 'string2datetime':
                    $time = strtotime($object);
                    $risposta = date(FieldTypeUtils::getEnvVar("DATETIME_FORMAT","d/m/Y H:i"),$time);
                    break;
                case 'string2date':
                    $time = strtotime($object);
                    $risposta = date(FieldTypeUtils::getEnvVar("DATE_FORMAT","d/m/Y"),$time);
                    break;
                case 'boolean':
                    $risposta = $object ? 'SI' : 'NO';
                    break;
                case 'string':
                default:
                    $risposta = $object;
                    break;
            }
        }

        return $risposta;
    }

    private function getObjectType($type, $object)
    {
        if (null === $type) {
            $tipo = is_object($object) ?
                    get_class($object) :
                    gettype($object);
        } else {
            $tipo = $type;
        }

        return $tipo;
    }

    private function getObjectProperty($field, $object)
    {
        $property = 'get'.Inflector::camelize(ucfirst($field));
        if (method_exists($object, $property)) {
            return $property;
        }
        $property = 'get'.ucfirst($field);
        if (method_exists($object, $property)) {
            return $property;
        }
        $property = 'is'.ucfirst($field);
        if (method_exists($object, $property)) {
            return $property;
        }
        $property = 'has'.ucfirst($field);
        if (method_exists($object, $property)) {
            return $property;
        }
    }
}
