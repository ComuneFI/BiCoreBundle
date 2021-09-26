<?php

namespace App\Form;

use App\Entity\Cliente;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ClienteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna cliente',
            'attr' => array(
                'class' => 'btn-outline-primary bisubmit',
        ), );

        $builder
                ->add('nominativo')
                ->add('attivo')
                ->add('datanascita', DateTimeType::class, array(
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'bidatepicker'),
                ))
                ->add('punti', NumberType::class)
                ->add('iscrittoil', DateTimeType::class, array(
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy HH:mm',
                    'attr' => array('class' => 'bidatetimepicker'),
                ))
                ->add('creditoresiduo', NumberType::class)
                ->add('note')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Cliente::class,
            'parametriform' => array(),
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'csrf_token_id' => "Cliente"
        ));
    }
}
