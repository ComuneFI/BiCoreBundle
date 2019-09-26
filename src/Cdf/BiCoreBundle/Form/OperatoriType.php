<?php

namespace Cdf\BiCoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Cdf\BiCoreBundle\Entity\Operatori;

class OperatoriType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitparms = array(
            'label' => 'Aggiorna record',
            'attr' => array(
                'class' => 'btn-outline-primary bisubmit',
                'aria-label' => 'Aggiorna record',
        ), );

        $builder
                ->add('operatore')
                ->add('username')
                ->add('email', EmailType::class)
                ->add('enabled')
                ->add('password', RepeatedType::class, array(
                    'type' => TextType::class,
                    'invalid_message' => 'Le password devono coincidere.',
                    'options' => array('attr' => array('class' => 'password-field')),
                    'required' => true,
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Ripeti Password'),
                ))
                ->add('ruoli')
                ->add('submit', SubmitType::class, $submitparms)
        //->add('salt')
        //->add('username_canonical')
        //->add('email_canonical')
        //->add('last_login')
        //->add('locked')
        //->add('expired')
        //->add('expires_at')
        //->add('confirmation_token')
        //->add('password_requested_at')
        //->add('roles')
        //->add('credentials_expired')
        //->add('credentials_expire_at')
        //->add('operatore')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Operatori::class,
            'parametriform' => array(),
        ]);
    }
}
