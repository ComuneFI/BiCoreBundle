<?php

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OpzionitabelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna record',
            'attr' => array(
                "class" => "btn-outline-primary bisubmit"
        ));

        $builder
                ->add('nometabella')
                ->add('descrizione')
                ->add('parametro')
                ->add('valore')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }
}
