<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productName', null, [ 'attr' => ['class' => 'd-block', 'class' => 'form-control' ]])
//            , array('attr' => array('class' => 'form-control','style' => 'margin-right:5px'))
            ->add('productPrice', null, [ 'attr' => ['class' => 'd-block', 'class' => 'form-control']])
//            ->add('productImage')
//            ->add('productTaxe')
            ->add('productDescription', null, [ 'attr' => ['class' => 'd-block', 'class' => 'form-control']])
//            ->add('createdAt')
//            ->add('updatedAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
