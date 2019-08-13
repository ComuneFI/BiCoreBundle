<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Entity\DoctrineFieldReader;
use Twig\Extension\AbstractExtension;
use Twig_SimpleFunction;

class ObjectToTabellaExtension extends AbstractExtension
{
    private $tableprefix;

    public function __construct($tableprefix)
    {
        $this->tableprefix = $tableprefix;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('object2view', array($this, 'object2View', 'is_safe' => array('html'))),
            new Twig_SimpleFunction('field2object', array($this, 'field2Object', 'is_safe' => array('html'))),
            new Twig_SimpleFunction('joinfieldid', array($this, 'joinFieldId', 'is_safe' => array('html'))),
        );
    }

    public function object2View($object, $type = null, $decodifiche = null)
    {
        $dfr = new DoctrineFieldReader($this->tableprefix);

        return $dfr->object2View($object, $type, $decodifiche);
    }

    public function field2Object($fieldname, $object, $decodifiche = null)
    {
        $dfr = new DoctrineFieldReader($this->tableprefix);

        return $dfr->getField2Object($fieldname, $object, $decodifiche);
    }

    public function joinFieldId($object)
    {
        $valore = null;
        if ($object) {
            $valore = $object->getId();
        }

        return $valore;
    }
}
