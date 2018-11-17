<?php

namespace App\Form;

use App\Entity\Ordine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OrdineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna ordine',
            'attr' => array(
                "class" => "btn-outline-primary bisubmit"
        ));


        $builder
                ->add('cliente')
                ->add('prodottofornitore')
                ->add('quantita')
                ->add('data', DateTimeType::class, array(
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy HH:mm',
                    "attr" => array("class" => "bidatetimepicker")
                ))
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }
}
