<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contactName', null, [ 'attr' => ['placeholder' => 'Name']])
            ->add('contactEmail', EmailType::class, [ 'attr' => ['placeholder' => 'Email']])
            ->add('contactSubject', null, [ 'attr' => ['placeholder' => 'Subject']])
            ->add('contactMessage', null, [ 'attr' => ['placeholder' => 'Your Message ...']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
