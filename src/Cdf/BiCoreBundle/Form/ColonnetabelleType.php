<?php

declare(strict_types=1);

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

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $submitparms = [
            'label' => 'Aggiorna record',
            'attr' => [
                'class' => 'btn-outline-primary bisubmit',
                'aria-label' => 'Aggiorna record',
            ],];

        $builder
                ->add('nometabella', null, ['label' => 'Tabella'])
                ->add('nomecampo', null, ['label' => 'Nome campo'])
                ->add('mostraindex', null, ['label' => 'Mostra in tabella'])
                ->add('ordineindex', null, ['label' => 'Ordine in tabella'])
                ->add('larghezzaindex', null, ['label' => 'Larghezza % in tabella', 'attr' => [
                        'min' => 0,
                        'max' => 100,
                ]])
                ->add('etichettaindex', null, ['label' => 'Etichetta in tabella'])
                ->add('registrastorico', null, ['label' => 'Registra in storico'])
                ->add('editabile', null, ['label' => 'Editabile'])
                ->add('operatori', EntityType::class, ['class' => Operatori::class, 'required' => false])
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Colonnetabelle::class,
            'parametriform' => [],
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'csrf_token_id' => "Colonnetabelle"]);
    }
}
