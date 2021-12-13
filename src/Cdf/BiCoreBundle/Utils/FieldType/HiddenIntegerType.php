<?php

namespace Cdf\BiCoreBundle\Utils\FieldType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class HiddenIntegerType extends HiddenType implements DataTransformerInterface
{

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->addModelTransformer($this);
    }

    /**
     *
     * @param mixed $data
     * @return int
     */
     
    public function transform($data) : int
    {
        return (int) $data;
    }

    /**
     *
     * @param string $data
     * @return int
     */
     
    public function reverseTransform($data) : int
    {
        return (int) $data;
    }

    public function getName() : string
    {
        return 'hidden_integer';
    }
}
