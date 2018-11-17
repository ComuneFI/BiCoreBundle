<?php

namespace Cdf\BiCoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Cdf\BiCoreBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class EntityHiddenType extends AbstractType
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new EntityToIdTransformer($this->objectManager, $options['class']);
        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
                ->setRequired(array('class'))
                ->setDefaults(array(
                    'invalid_message' => 'The entity does not exist.',
                    'query_builder' => null
                ))
        ;
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function getName()
    {
        return 'entity_hidden';
    }
}
