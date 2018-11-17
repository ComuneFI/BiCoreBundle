<?php

namespace App\Form;

use App\Entity\Fornitore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FornitoreType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna fornitore',
            'attr' => array(
                "class" => "btn-outline-primary bisubmit"
        ));
        
        $builder
                ->add('ragionesociale')
                ->add('partitaiva')
                ->add('capitalesociale')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Fornitore::class,
        ]);
    }
}
