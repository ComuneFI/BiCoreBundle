<?php

namespace Cdf\BiCoreBundle\Utils\Entity;

class DoctrineFieldReader
{

    private $bitableprefix;

    public function __construct()
    {
        $this->bitableprefix = getenv("bicorebundle_table_prefix");
    }

    public function getField2Object($fieldtoobj, $object)
    {
        $property = "";
        $field = "";
        $propertyfound = false;
        $subfields = explode(".", str_replace($this->bitableprefix, "", $fieldtoobj));
        foreach ($subfields as $field) {
            $property = $this->getObjectProperty($field, $object);
            if ($property) {
                $object = $object->$property();
                $propertyfound = true;
            }
        }
        if (!$propertyfound) {
            throw new \Exception("Proprietà " . $field . " non trovata per " . $fieldtoobj);
        }
        return $object;
    }

    public function object2View($object, $type = null)
    {
        $risposta = null;

        if (!is_null($object)) {
            if ($type === null) {
                $tipo = is_object($object) ?
                        get_class($object) :
                        gettype($object);
            } else {
                $tipo = $type;
            }
            switch (strtolower($tipo)) {
                case 'array':
                    $risposta = print_r($object, true);
                    break;
                case 'date':
                    $risposta = $object->format("d/m/Y");
                    break;
                case 'datetime':
                    $risposta = $object->format("d/m/Y H:i");
                    break;
                case 'boolean':
                    $risposta = $object ? "SI" : "NO";
                    break;
                case 'string':
                default:
                    $risposta = $object;
                    break;
            }
        }
        return $risposta;
    }

    private function getObjectProperty($field, $object)
    {
        $property = "get" . ucfirst($field);
        if (method_exists($object, $property)) {
            return $property;
        }
        $property = "is" . ucfirst($field);
        if (method_exists($object, $property)) {
            return $property;
        }
        $property = "has" . ucfirst($field);
        if (method_exists($object, $property)) {
            return $property;
        }
    }
}
