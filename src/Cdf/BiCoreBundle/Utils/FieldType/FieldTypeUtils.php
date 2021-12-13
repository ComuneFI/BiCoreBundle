<?php

namespace Cdf\BiCoreBundle\Utils\FieldType;

use DateTime;

/**
 * @author manzolo
 */
abstract class FieldTypeUtils
{
    /**
     *
     * @param array<mixed>|null $value
     * @return array<mixed>
     */
    public static function getArrayValue($value)
    {
        if (is_null($value)) {
            $newval = array();
        } else {
            return $value;
        }

        return $newval;
    }

    /**
     *
     * @param bool|null $value
     * @return bool|null
     */
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

    /**
     *
     * @param int $value
     * @return \DateTime
     */
    public static function getDateTimeValueFromTimestamp($value)
    {
        $date = new DateTime();
        $date->setTimestamp($value);

        return $date;
    }

    /**
     *
     * @param mixed $value
     * @return \DateTime|null
     */
    public static function extractDateTime($value)
    {
        $date = null;
        if (is_string($value) || (is_array($value) && (isset($value['date'])))) {
            switch (true) {
                case isset($value['date']):
                    $date = new Datetime($value['date']);
                    break;
                case DateTime::createFromFormat(\DateTime::ISO8601, $value):
                    $date = DateTime::createFromFormat(\DateTime::ISO8601, $value);
                    break;
                case DateTime::createFromFormat('Y-m-d H:i:s', $value):
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    break;
                case DateTime::createFromFormat('Y-m-d', $value):
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $value.' 00:00:00');
                    break;
                default:
                    break;
            }
        }

        return $date;
    }

    /**
     * Try to read the .env Key value provided as $key, otherwise return the default value
     */
    public static function getEnvVar(string $key, string $defaultValue) : string
    {
        return (getenv($key)===false)?$defaultValue:getenv($key);
    }
}
