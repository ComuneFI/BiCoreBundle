<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

class ParametriTabella
{
    /**
     *
     * @throws \Exception
     */
    public static function getParameter(string $parametro): string
    {
        return base64_decode($parametro);
    }
    
    /**
     *
     * @param ?mixed $parametro
     */
    public static function setParameter($parametro): string
    {
        return base64_encode($parametro);
    }
}
