<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;

class ParametriTabellaTwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('getparametrotabella', array($this, 'getParametroTabella')),
            new \Twig_SimpleFilter('setparametrotabella', array($this, 'setParametroTabella')),
        );
    }
    public function getParametroTabella($parametro)
    {
        return ParametriTabella::getParameter($parametro);
    }
    public function setParametroTabella($parametro)
    {
        return ParametriTabella::setParameter($parametro);
    }
}
