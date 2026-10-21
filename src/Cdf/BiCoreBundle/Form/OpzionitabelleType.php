<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Cdf\BiCoreBundle\Entity\Opzionitabelle;

class OpzionitabelleType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $submitparms = [
            'label' => 'Aggiorna record',
            'attr' => [
                'class' => 'btn-outline-primary bisubmit',
                'aria-label' => 'Aggiorna record',
            ],];

        $builder
                ->add('nometabella')
                ->add('descrizione')
                ->add('parametro')
                ->add('valore')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Opzionitabelle::class,
            'parametriform' => [],
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'csrf_token_id' => "Opzionitabelle"]);
    }
}
