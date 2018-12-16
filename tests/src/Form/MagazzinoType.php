<?php

namespace App\Form;

use App\Entity\Magazzino;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MagazzinoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna record',
            'attr' => array(
                'class' => 'btn-outline-primary bisubmit',
        ), );
        $builder
                ->add('ordine')
                ->add('evaso')
                ->add('giornodellasettimana')
                ->add('dataspedizione', DateTimeType::class, array(
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy HH:mm',
                    'attr' => array('class' => 'bidatetimepicker'),
                ))
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Magazzino::class,
            'parametriform' => array(),
        ]);
    }
}
