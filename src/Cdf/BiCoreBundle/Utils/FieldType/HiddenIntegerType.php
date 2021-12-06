<?php

namespace Cdf\BiCoreBundle\Utils\FieldType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class HiddenIntegerType extends HiddenType implements DataTransformerInterface
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        return (int) $data;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        return (int) $data;
    }

    public function getName()
    {
        return 'hidden_integer';
    }
}
