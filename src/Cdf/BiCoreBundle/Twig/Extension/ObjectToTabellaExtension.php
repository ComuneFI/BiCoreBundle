<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Entity\DoctrineFieldReader;

class ObjectToTabellaExtension extends \Twig_Extension
{

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('object2view', array($this, 'object2View', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('field2object', array($this, 'field2Object', 'is_safe' => array('html'))),
        );
    }

    public function object2View($object, $type = null)
    {
        $dfr = new DoctrineFieldReader();
        return $dfr->object2View($object, $type);
    }

    public function field2Object($fieldtoobj, $object)
    {
        $dfr = new DoctrineFieldReader();
        return $dfr->getField2Object($fieldtoobj, $object);
    }
}
