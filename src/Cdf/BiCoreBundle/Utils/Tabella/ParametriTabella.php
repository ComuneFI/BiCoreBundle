<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

class ParametriTabella
{
    /**
     *
     * @param string $parametro
     * @return string
     * @throws \Exception
     */
    public static function getParameter(string $parametro): string
    {
        return base64_decode($parametro);
    }
    
    /**
     *
     * @param ?mixed $parametro
     * @return string
     */
    public static function setParameter($parametro): string
    {
        return base64_encode($parametro);
    }
}
