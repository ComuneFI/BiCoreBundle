<?php

namespace Cdf\BiCoreBundle\Utils\StdClass;

class StdClassUtils
{
    public static function arrayToObject(array $array, $className)
    {
        return unserialize(sprintf(
            'O:%d:"%s"%s',
            strlen($className),
            $className,
            strstr(serialize($array), ':')
        ));
    }
}
