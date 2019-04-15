<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

class HeaderTabellaExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('sort_class', array($this, 'sortClass', 'is_safe' => array('html'))),
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
