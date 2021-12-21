<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

//            ->add('roles')
//            ->add('password')
            ->add('firstName', null , array(
                'label' => false,
                'required' => true,
                'attr' => [ 'class' => 'd-inline-block']
            ))
            ->add('lastName', null , array(
                'label' => false,
                'required' => true,
                'attr' => [ 'class' => 'd-inline-block']
            ))
            ->add('country', null , array(
                'label' => false,
                'required' => true,
            ))
//            RepeatedType::class
            ->add('address', null , array(
                'label' => false,
                'required' => true,
//                'first_options'  => ['label' => false],
//                'second_options' => ['label' => false],
            ))
            ->add('city', null , array(
                'label' => false,
                'required' => true,
            ))
            ->add('state', null , array(
                'label' => false,
                'required' => true,
            ))
            ->add('postCode', null , array(
                'label' => false,
                'required' => true,
            ))
            ->add('phone', null , array(
                'label' => false,
                'required' => true,
            ))
            ->add('email', null , array(
                'label' => false,
                'required' => true,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
