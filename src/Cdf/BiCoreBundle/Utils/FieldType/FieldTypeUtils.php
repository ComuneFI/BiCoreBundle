<?php

namespace Cdf\BiCoreBundle\Utils\FieldType;

/**
 * @author manzolo
 */
abstract class FieldTypeUtils
{
    public static function getArrayValue($value)
    {
        if (is_null($value)) {
            $newval = array();
        } else {
            return $value;
        }

        return $newval;
    }

    public static function getBooleanValue($value)
    {
        if (is_null($value)) {
            $newval = null;
        } else {
            if ($value) {
                $newval = true;
            } else {
                $newval = false;
            }
        }

        return $newval;
    }

    public static function getDateTimeValueFromTimestamp($value)
    {
        $date = new \DateTime();
        $date->setTimestamp($value);

        return $date;
    }

    public static function extractDateTime($value)
    {
        $date = null;
        if (is_string($value) || (is_array($value) && (isset($value['date'])))) {
            switch (true) {
                case isset($value['date']):
                    $date = new \Datetime($value['date']);
                    break;
                case \DateTime::createFromFormat('Y-m-d H:i:s', $value):
                    $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    break;
                case \DateTime::createFromFormat('Y-m-d', $value):
                    $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value.' 00:00:00');
                    break;
                default:
                    break;
            }
        }

        return $date;
    }
}
