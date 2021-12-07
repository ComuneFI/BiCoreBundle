<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HeaderTabellaExtension extends AbstractExtension
{

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('sort_class', [$this, 'sortClass']),
        );
    }
    /**
     *
     * @param string $colonneordinamento
     * @param array<mixed> $modellocampo
     * @return string
     */
    public function sortClass(string $colonneordinamento, array $modellocampo): string
    {
        if (true === $modellocampo['association'] || true === $modellocampo['campoextra']) {
            $sorttype = '';
        } else {
            $cursort = json_decode($colonneordinamento, true);
            if (array_key_exists($modellocampo['nomecampo'], $cursort)) {
                if ('ASC' == $cursort[$modellocampo['nomecampo']]) {
                    $sorttype = 'sorting_asc';
                } else {
                    $sorttype = 'sorting_desc';
                }
            } else {
                $sorttype = 'sorting';
            }
        }

        return $sorttype;
    }
}
