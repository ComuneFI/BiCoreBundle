<?php

namespace App\Form;

use App\Entity\Ordine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Cliente;

class OrdineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna ordine',
            'attr' => array(
                'class' => 'btn-outline-primary bisubmit',
        ), );

        if (isset($options['parametriform']['cliente_id']) && $options['parametriform']['cliente_id']) {
            $builder->add('cliente', null, array('required' => true, 'class' => Cliente::class,
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('c')->where('c.id = :id')->setParameter('id', $options['parametriform']['cliente_id']);
                }, ));
        } else {
            $builder->add('cliente');
        }

        $builder->add('prodottofornitore')
                ->add('quantita')
                ->add('data', DateTimeType::class, array(
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy HH:mm',
                    'attr' => array('class' => 'bidatetimepicker'),
                ))
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Ordine::class,
            'parametriform' => array(),
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'csrf_token_id' => "Ordine"
        ));
    }
}
