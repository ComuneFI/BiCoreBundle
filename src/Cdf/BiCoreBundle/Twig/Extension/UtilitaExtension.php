<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UtilitaExtension extends AbstractExtension
{
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('json_decode', [$this, 'jsonDecode']),
            new TwigFunction('parameter', [$this, 'getParameter']),
        );
    }

    public function getFilters(): array
    {
        return array(
            new TwigFilter('getparametrotabella', array($this, 'getParametroTabella')),
            new TwigFilter('larghezzacolonna', array($this, 'getLarghezzacolonna')),
        );
    }

    /**
     *
     * @param string $string
     * @return mixed
     */
    public function jsonDecode(string $string)
    {
        return json_decode($string);
    }

    /**
     *
     * @param string $parameter
     * @return mixed
     */
    public function getParameter(string $parameter)
    {
        return $this->parameterBag->get($parameter);
    }

    /**
     *
     * @param string $parametro
     * @return string|false
     */
    public function getParametroTabella(string $parametro)
    {
        return ParametriTabella::getParameter($parametro);
    }

    public function getLarghezzacolonna(?string $larghezza): string
    {
        $class = 'biw-5';
        if ($larghezza) {
            $class = 'biw-' . $larghezza;
        }

        return $class;
    }
}
