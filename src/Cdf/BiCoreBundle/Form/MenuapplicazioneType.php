<?php

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Cdf\BiCoreBundle\Entity\Menuapplicazione;

class MenuapplicazioneType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna record',
            'attr' => array(
                "class" => "btn-outline-primary bisubmit"
        ));
        $builder
                ->add('nome')
                ->add('percorso')
                ->add("padre")
                ->add('ordine')
                ->add('attivo')
                ->add('target')
                ->add('tag')
                ->add('notifiche')
                ->add('autorizzazionerichiesta')
                ->add('percorsonotifiche')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Menuapplicazione::class,
            'parametriform' => array()
        ]);
    }
}
