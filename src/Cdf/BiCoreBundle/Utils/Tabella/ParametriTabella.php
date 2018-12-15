<?php

namespace Cdf\BiCoreBundle\Utils\Tabella;

class ParametriTabella
{
    public static function getParameter($parametro)
    {
        return base64_decode($parametro);
    }

    public static function setParameter($parametro)
    {
        return base64_encode($parametro);
    }
}
