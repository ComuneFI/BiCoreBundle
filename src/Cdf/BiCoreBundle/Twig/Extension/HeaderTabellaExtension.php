<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

class HeaderTabellaExtension extends \Twig_Extension
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
        if ($modellocampo["association"] === true || $modellocampo["campoextra"] === true) {
            $sorttype = "";
        } else {
            $cursort = json_decode($colonneordinamento, true);
            if (array_key_exists($modellocampo["nomecampo"], $cursort)) {
                if ($cursort[$modellocampo["nomecampo"]] == 'ASC') {
                    $sorttype = "sorting_asc";
                } else {
                    $sorttype = "sorting_desc";
                }
            } else {
                $sorttype = "sorting";
            }
        }

        return $sorttype;
    }
}
