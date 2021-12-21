<?php

namespace App\Form;

use App\Entity\Blog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BlogFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [ 'attr' => ['class' => 'd-block', 'class' => 'form-control','placeholder' => 'Name', ]])
            ->add('description', null, [ 'attr' => ['class' => 'd-block', 'class' => 'form-control','placeholder' => 'Name','required' => false ]])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => '...',
                'download_uri' => false,
                'imagine_pattern' => 'squared_thumbnail_medium',
                'attr' => ['class' => 'form-control'],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
