<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Utils\FieldType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class HiddenIntegerType extends HiddenType implements DataTransformerInterface
{

    /**
     *
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->addModelTransformer($this);
    }

    /**
     *
     * @param mixed $data
     */
    public function transform($data) : int
    {
        return (int) $data;
    }

    /**
     *
     * @param string $data
     */
    public function reverseTransform($data) : int
    {
        return (int) $data;
    }

    public function getBlockPrefix() : string
    {
        return 'hidden_integer';
    }
}
