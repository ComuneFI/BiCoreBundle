<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Tabella\ParametriTabella;

class HeaderTabellaExtension extends \Twig_Extension
{

    public $container;

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
