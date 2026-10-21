<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Cdf\BiCoreBundle\Entity\Permessi;

class PermessiType extends AbstractType
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
                ->add('modulo')
                ->add('crud')
                ->add('operatori')
                ->add('ruoli')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Permessi::class,
            'parametriform' => [],
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'csrf_token_id' => "Permessi"]);
    }
}
