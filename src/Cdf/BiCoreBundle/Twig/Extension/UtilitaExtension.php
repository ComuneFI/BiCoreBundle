<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UtilitaExtension extends AbstractExtension
{
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('json_decode', $this->jsonDecode(...)),
            new TwigFunction('parameter', $this->getParameter(...)),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('getparametrotabella', $this->getParametroTabella(...)),
            new TwigFilter('larghezzacolonna', $this->getLarghezzacolonna(...)),
        ];
    }

    public function jsonDecode(string $string): mixed
    {
        return json_decode($string);
    }

    public function getParameter(string $parameter): bool|string|int|float|\UnitEnum|array|null
    {
        return $this->parameterBag->get($parameter);
    }

    /**
     *
     * @return string|false
     */
    public function getParametroTabella(string $parametro)
    {
        return ParametriTabella::getParameter($parametro);
    }

    public function getLarghezzacolonna(?string $larghezza): string
    {
        if ($larghezza) {
            return 'biw-' . $larghezza;
        }

        return 'biw-5';
    }
}
