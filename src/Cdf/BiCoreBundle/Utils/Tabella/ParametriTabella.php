<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

class ParametriTabella
{
    /**
     *
     * @param string $parametro
     * @return string|false
     */
    public static function getParameter(string $parametro)
    {
        return base64_decode($parametro);
    }
    
    /**
     *
     * @param ?mixed $parametro
     * @return string
     */
    public static function setParameter($parametro)
    {
        return base64_encode($parametro);
    }
}
