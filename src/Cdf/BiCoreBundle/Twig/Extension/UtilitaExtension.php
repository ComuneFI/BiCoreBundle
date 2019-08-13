<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Twig\Extension\AbstractExtension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class UtilitaExtension extends AbstractExtension
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('json_decode', array($this, 'jsonDecode', 'is_safe' => array('html'))),
            new Twig_SimpleFunction('parameter', array($this, 'getParameter', 'is_safe' => array('html'))),
        );
    }

    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('getparametrotabella', array($this, 'getParametroTabella')),
            new Twig_SimpleFilter('larghezzacolonna', array($this, 'getLarghezzacolonna')),
        );
    }

    public function jsonDecode($string)
    {
        return json_decode($string);
    }

    public function getParameter($parameter)
    {
        return $this->container->getParameter($parameter);
    }

    public function getParametroTabella($parametro)
    {
        return ParametriTabella::getParameter($parametro);
    }

    public function getLarghezzacolonna($larghezza)
    {
        $class = 'biw-5';
        if ($larghezza) {
            $class = 'biw-'.$larghezza;
        }

        return $class;
    }
}
