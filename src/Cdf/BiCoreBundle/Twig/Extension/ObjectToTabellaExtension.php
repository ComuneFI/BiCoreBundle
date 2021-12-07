<?php

namespace Cdf\BiCoreBundle\Twig\Extension;

use Cdf\BiCoreBundle\Utils\Entity\DoctrineFieldReader;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ObjectToTabellaExtension extends AbstractExtension
{
    private string $tableprefix;

    public function __construct(string $tableprefix)
    {
        $this->tableprefix = $tableprefix;
    }

    public function getFunctions(): array
    {
        return array(
            new TwigFunction('object2view', [$this, 'object2View']),
            new TwigFunction('field2object', [$this, 'field2Object']),
            new TwigFunction('joinfieldid', [$this, 'joinFieldId']),
        );
    }

    /**
     *
     * @param mixed $object
     * @param string $type
     * @param array<mixed> $decodifiche
     * @return mixed|null
     */
    public function object2View($object, ?string $type = null, $decodifiche = null)
    {
        $dfr = new DoctrineFieldReader($this->tableprefix);

        return $dfr->object2View($object, $type, $decodifiche);
    }

    /**
     *
     * @param string $fieldname
     * @param mixed $object
     * @param array<mixed> $decodifiche
     * @return mixed|null
     */
    public function field2Object($fieldname, $object, $decodifiche = null)
    {
        $dfr = new DoctrineFieldReader($this->tableprefix);

        return $dfr->getField2Object($fieldname, $object, $decodifiche);
    }

    /**
     *
     * @param mixed $object
     * @return int|null
     */
    public function joinFieldId($object)
    {
        $valore = null;
        if ($object) {
            $valore = $object->getId();
        }

        return $valore;
    }
}
