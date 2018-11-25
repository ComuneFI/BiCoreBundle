<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

class UtilitaExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('json_decode', array($this, 'jsonDecode', 'is_safe' => array('html'))),
        );
    }
    
    public function jsonDecode($string)
    {
        return json_decode($string);
    }
}
