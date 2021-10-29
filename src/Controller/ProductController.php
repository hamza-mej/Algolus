<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
//    #[Route('/product', name: 'app_product')]
//    public function index(ProductRepository $productRepository): Response
//    {
//
//        $product = $productRepository->findAll();
//        return $this->render('dashboard/Product/index.html.twig', [
//            'products' => $product,
//        ]);
//    }

//    #[Route('/new', name: 'app_new')]
//    public function new(): Response
//    {
//
////        $product = $productRepository->findAll();
//        return $this->render('dashboard/Product/new.html.twig', [
//
//        ]);
//    }

    #[Route('/show{id}', name: 'app_show')]
    public function show(Product $product): Response
    {

        return $this->render('dashboard/Product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product', name: 'app_product')]
    public function create(ProductRepository $productRepository, Request $request,EntityManagerInterface $em): Response
    {
        $products = $productRepository->findAll();
        $product = new Product;
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('app_product');
        }
        return $this->render('dashboard/Product/index.html.twig', [
            'CreateForm' => $form->createView(),
            'products' => $products,
        ]);
    }

    #[Route('/edit{id}', name: 'app_edit', methods: ['GET','POST'])]
    public function edit(Request $request,EntityManagerInterface $em,Product $product): Response
    {

        if($request->request){
            $product->setProductName($request->get("nomClient"));
            $product->setProductPrice($request->get("Price"));
            $product->setProductDescription($request->get("description"));

        }
        $em->flush();
        return $this->redirectToRoute('app_product');


//        $form = $this->createForm(ProductFormType::class, $product);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em->flush();
//
//            return $this->redirectToRoute('app_product');
//        }
//        return $this->render('dashboard/Product/edit.html.twig', [
//            'product' => $product,
////            'EditForm' => $form->createView(),
//        ]);
    }

    #[Route('/delete{id}', name: 'app_delete', methods: ['GET','POST'])]
    public function delete(EntityManagerInterface $em, Product $product): Response
    {
        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('app_product');
    }
}
