<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('CategoryName', null, [ 'attr' => ['class' => 'form-control','placeholder' => 'Name', ]])
            ->add('CategoryImage', FileType::class, [ 'mapped' => false ,'attr' => ['class' => 'col-md-12 d-flex justify-content-center form-control','placeholder' => 'Image', ]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
