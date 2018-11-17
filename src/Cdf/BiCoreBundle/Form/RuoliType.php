<?php

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RuoliType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna record',
            'attr' => array(
                "class" => "btn-outline-primary bisubmit"
        ));

        $builder
                ->add('ruolo')
                ->add('paginainiziale')
                ->add('superadmin')
                ->add('admin')
                ->add('user')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }
}
