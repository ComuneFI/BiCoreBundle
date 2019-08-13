<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig_SimpleFunction;

class HeaderTabellaExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('sort_class', array($this, 'sortClass', 'is_safe' => array('html'))),
        );
    }

    public function sortClass($colonneordinamento, $modellocampo)
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
