<?php

namespace App\Form;

use App\Entity\Prodottofornitore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProdottofornitoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna prodotto',
            'attr' => array(
                "class" => "btn-outline-primary bisubmit"
        ));
        $builder
                ->add('fornitore')
                ->add('descrizione')
                ->add('quantitadisponibile')
                ->add('submit', SubmitType::class, $submitparms)
        ;
    }
}
