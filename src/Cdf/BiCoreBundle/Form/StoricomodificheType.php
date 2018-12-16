<?php

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Cdf\BiCoreBundle\Entity\Storicomodifiche;

class StoricomodificheType extends AbstractType
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
                'class' => 'btn-outline-primary bisubmit',
        ), );

        $builder
                ->add('nometabella')
                ->add('nomecampo')
                ->add('idtabella')
                ->add('giorno')
                ->add('valoreprecedente')
                ->add('operatori_id')
                ->add('operatori')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Storicomodifiche::class,
            'parametriform' => array(),
        ]);
    }
}
