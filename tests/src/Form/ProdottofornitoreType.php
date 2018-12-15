<?php

namespace App\Form;

use App\Entity\Prodottofornitore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProdottofornitoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna prodotto',
            'attr' => array(
                'class' => 'btn-outline-primary bisubmit',
        ), );
        $builder
                ->add('fornitore')
                ->add('descrizione')
                ->add('quantitadisponibile')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Prodottofornitore::class,
            'parametriform' => array(),
        ));
    }
}
