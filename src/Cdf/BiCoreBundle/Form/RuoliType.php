<?php

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Cdf\BiCoreBundle\Entity\Ruoli;

class RuoliType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna record',
            'attr' => array(
                'class' => 'btn-outline-primary bisubmit',
                'aria-label' => 'Aggiorna record',
        ), );

        $builder
                ->add('ruolo')
                ->add('paginainiziale')
                ->add('superadmin')
                ->add('admin')
                ->add('user')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ruoli::class,
            'parametriform' => array(),
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'csrf_token_id' => "Ruoli",
        ]);
    }
}
