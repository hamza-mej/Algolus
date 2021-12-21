<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Product;
use App\Entity\Size;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Vich\UploaderBundle\Form\Type\VichImageType;


class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productName', null, ['attr' => ['class' => 'd-block', 'class' => 'form-control', 'placeholder' => 'Name',]])
//            , array('attr' => array('class' => 'form-control','style' => 'margin-right:5px'))
            ->add('productPrice', null, ['attr' => ['class' => 'd-block', 'class' => 'form-control', 'placeholder' => 'Price',]])
//            ->add('productImage', DropzoneType::class)
//            ->add('productTaxe')
            ->add('productDescription', null, ['attr' => ['class' => 'd-block', 'class' => 'form-control', 'placeholder' => 'Description',]])
//            ->add('createdAt')
//            ->add('updatedAt')

            ->add('category', EntityType::class, [
                'class' => category::class,
                'placeholder' => 'select a category',
                'attr' => ['class' => 'd-block', 'class' => 'form-control'],
            ])
            ->add('addColor', EntityType::class, [
                'class' => color::class,
                'placeholder' => 'select a color',
                'mapped' => false,
                'multiple' => true ,
                'attr' => ['class' => 'd-block', 'class' => 'form-control', 'class' => 'select2-multiple'],
            ])
            ->add('size', EntityType::class, [
                'class' => size::class,
                'placeholder' => 'select a size',
                'attr' => ['class' => 'd-block', 'class' => 'form-control'],
            ])
            ->add('onSale', CheckboxType::class, [
                'label' => 'En promotion',
                'required' => false,
            ]);
//            ->add("category", category::class, array(
//                "class"=>"AcmeTotoBundle:Categorie",
//                "choices" => $listeCategorie,
//                "property"=>"nomCategorie"
//            ))
//            ->add('imageFile', VichImageType::class, [
//                'required' => false,
//                'allow_delete' => true,
//                'delete_label' => '...',
//                'download_uri' => false,
//                'imagine_pattern' => 'squared_thumbnail_medium',
//                'attr' => ['class' => 'form-control'],
//
//            ]);
        ;

//        $builder->get('category')->addEventListener(
//            FormEvents::PRE_SUBMIT,
//            function (FormEvent $event){
//                $form = $event->getForm();
//            }
//        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
