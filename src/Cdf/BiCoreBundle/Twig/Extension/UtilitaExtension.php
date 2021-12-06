<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

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
    public function getFunctions() : array
    {
        return array(
            new TwigFunction('json_decode', [$this, 'jsonDecode']),
            new TwigFunction('parameter', [$this, 'getParameter']),
        );
    }

    public function getFilters() : array
    {
        return array(
            new TwigFilter('getparametrotabella', array($this, 'getParametroTabella')),
            new TwigFilter('larghezzacolonna', array($this, 'getLarghezzacolonna')),
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
