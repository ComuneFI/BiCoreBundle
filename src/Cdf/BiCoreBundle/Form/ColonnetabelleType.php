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
                'class' => 'btn-outline-primary bisubmit',
                'aria-label' => 'Aggiorna record',
            ),);

        $builder
                ->add('nometabella', null, array('label' => 'Tabella'))
                ->add('nomecampo', null, array('label' => 'Nome campo'))
                ->add('mostraindex', null, array('label' => 'Mostra in tabella'))
                ->add('ordineindex', null, array('label' => 'Ordine in tabella'))
                ->add('larghezzaindex', null, array('label' => 'Larghezza % in tabella', 'attr' => array(
                        'min' => 0,
                        'max' => 100,
                )))
                ->add('etichettaindex', null, array('label' => 'Etichetta in tabella'))
                ->add('registrastorico', null, array('label' => 'Registra in storico'))
                ->add('editabile', null, array('label' => 'Editabile'))
                ->add('operatori', EntityType::class, array('class' => Operatori::class, 'required' => false))
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Colonnetabelle::class,
            'parametriform' => array(),
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'csrf_token_id' => "Colonnetabelle"]);
    }
}
