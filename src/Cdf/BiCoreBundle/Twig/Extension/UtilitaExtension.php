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
            new \Twig_SimpleFilter('larghezzacolonna', array($this, 'getLarghezzacolonna')),
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
        $class = "col-auto";
        if ($larghezza) {
            if ($larghezza <= 100) {
                $class = "col-sm-1";
            } elseif ($larghezza > 100 && $larghezza <= 200) {
                $class = "col-sm-2";
            } elseif ($larghezza > 200 && $larghezza <= 300) {
                $class = "col-sm-3";
            } elseif ($larghezza > 300 && $larghezza <= 400) {
                $class = "col-sm-4";
            } elseif ($larghezza > 400 && $larghezza <= 500) {
                $class = "col-sm-5";
            } else {
                $class = "col-sm-6";
            }
        }
        return $class;
    }
}
