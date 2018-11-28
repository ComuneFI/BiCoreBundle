<?php

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Cdf\BiCoreBundle\Entity\Operatori;
use Cdf\BiCoreBundle\Entity\Colonnetabelle;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ColonnetabelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna record',
            'attr' => array(
                "class" => "btn-outline-primary bisubmit"
        ));

        $builder
                ->add('nometabella', null, array('label' => 'Tabella'))
                ->add('nomecampo')
                ->add('mostraindex')
                ->add('ordineindex')
                ->add('larghezzaindex')
                ->add('etichettaindex')
                ->add('mostrastampa')
                ->add('ordinestampa')
                ->add('larghezzastampa')
                ->add('registrastorico')
                ->add('operatori', EntityType::class, array('class' => Operatori::class, 'required' => false))
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Colonnetabelle::class,
            'parametriform' => array()
        ]);
    }
}
