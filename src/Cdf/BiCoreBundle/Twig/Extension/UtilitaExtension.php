<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;

class UtilitaExtension extends \Twig_Extension
{
    public $container;
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('json_decode', array($this, 'jsonDecode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('parameter', array($this, 'getParameter', 'is_safe' => array('html'))),
        );
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('getparametrotabella', array($this, 'getParametroTabella')),
        );
    }

    public function jsonDecode($string)
    {
        return json_decode($string);
    }
    public function getParameter($parameter)
    {
        return  $this->container->getParameter($parameter);
    }
    public function getParametroTabella($parametro)
    {
        return ParametriTabella::getParameter($parametro);
    }
}
