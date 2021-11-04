<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function create(ProductRepository $productRepository, Request $request,
                           EntityManagerInterface $em,PaginatorInterface $paginator): Response
    {
        $data = $productRepository->findAll();
        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            3
        );

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

    #[Route('/productshow{id}', name: 'app_show_product')]
    public function show(Product $product): Response
    {

        return $this->render('dashboard/Product/show.html.twig', [
            'product' => $product,
        ]);
    }


    #[Route('/productedit{id}', name: 'app_edit_product', methods: ['GET','POST'])]
    public function edit(Request $request,EntityManagerInterface $em,Product $product): Response
    {

        if($request->request){
            $product->setProductName($request->get("nomCategory"));
            $product->setProductPrice($request->get("nomPrice"));
            $product->setProductDescription($request->get("nomDescription"));

        }
        $em->flush();
        return $this->redirectToRoute('app_product');
    }

    #[Route('/productdelete{id}', name: 'app_delete_product', methods: ['GET','POST'])]
    public function delete(EntityManagerInterface $em, Product $product): Response
    {
        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('app_product');
    }
}
