<?php

declare(strict_types=1);

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Cdf\BiCoreBundle\Entity\Menuapplicazione;
use Cdf\BiCoreBundle\Form\Datatransformer\MenuapplicazioneTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class MenuapplicazioneType extends AbstractType
{

    public function __construct(private readonly MenuapplicazioneTransformer $transformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $submitparms = [
            'label' => 'Aggiorna record',
            'attr' => [
                'class' => 'btn-outline-primary bisubmit',
                'aria-label' => 'Aggiorna record',
            ],];
        $builder
                ->add('nome')
                ->add('percorso')
                ->add('padre', EntityType::class, [
                    'class' => Menuapplicazione::class,
                    // validation message if the data transformer fails
                    'invalid_message' => 'Padre non valido',
                    'required' => false
                ])
                ->add('ordine')
                ->add('attivo')
                ->add('target')
                ->add('tag')
                ->add('notifiche')
                ->add('autorizzazionerichiesta')
                ->add('percorsonotifiche')
                ->add('submit', SubmitType::class, $submitparms);

        $builder->get('padre')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menuapplicazione::class,
            'parametriform' => [],
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'csrf_token_id' => "Menuapplicazione"]);
    }
}
