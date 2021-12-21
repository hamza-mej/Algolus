<?php

namespace App\Controller\Dashboard;

use App\Entity\Product;

//use App\Form\ProductFormType;
use App\Form\ProductFormType;
use App\Form\SearchAnnonceType;
use App\Repository\CategoryRepository;
use App\Repository\ColorRepository;
use App\Repository\ProductRepository;
use App\Repository\SizeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    #[Route('/dashboard-Product', name: 'app_product')]
    public function index(CategoryRepository $categoryRepository, ColorRepository $colorRepository, SizeRepository $sizeRepository, Request $request,ProductRepository $productRepository,PaginatorInterface $paginator): Response
    {
        $category = $categoryRepository->findAll();
        $color = $colorRepository->findAll();
        $size = $sizeRepository->findAll();
        $data = $productRepository->findAll();
        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            4
        );

        return $this->render('dashboard/Product/index.html.twig', [
//            'CreateForm' => $form->createView(),
//            'SearchForm' => $forms->createView(),
            'products' => $products,
            'category' => $category,
            'color' => $color,
            'size' => $size,
        ]);
    }


    #[Route('/create-product', name: 'app_product_create')]
    public function create(CategoryRepository $categoryRepository, ColorRepository $colorRepository,
                           SizeRepository $sizeRepository, Request $request,
                           EntityManagerInterface $em, ): Response
    {



        $data = $request->request->all();
        $product = new Product;

        $file = $request->files->get('productImage');
        if($file){
            $upload_dir_product = $this->getParameter('upload_dir_product');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_product,
                $filename
            );
            $product->setProductImage($filename);
        }
//        $form = $this->createForm(ProductFormType::class, $product);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em->persist($product);
//            $em->flush();
//
//            return $this->redirectToRoute('app_product');
//        }
//        dd($request->request->get("nomProduct"));
        $product
//            ->setProductImage()
            ->setProductName($data["nomProduct"])
            ->setProductPrice($data["nomPrice"])
            ->setProductDescription($data["nomDescription"])
            ->setCategory($categoryRepository->findOneById($data["selectCategory"]));
        if (!empty($request->get("promo"))) {
            $product->setOnSale($data["promo"]);
        } else {
            $product->setOnSale(false);
        }

        $product->getColor()->clear();
        foreach ($request->get("selectColor") as $color)
            $product->addColor($colorRepository->find($color));

        $product->getSize()->clear();
        foreach ($request->get("selectSize") as $size)
            $product->addSize($sizeRepository->find($size));

//        $size = $sizeRepository->findOneById($request->get("selectSize"));
//        $product->setSize($size);

        $em->persist($product);
        $em->flush();

        return $this->redirectToRoute('app_product');
    }

    #[Route('/dashProductlist', name: 'app_product_list')]
    public function createList(ProductRepository      $productRepository, Request $request,
                               EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $data = $productRepository->findAll();
        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            8
        );

        $product = new Product;
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('app_product_list');
        }
        return $this->render('dashboard/Product/list.html.twig', [
            'CreateForm' => $form->createView(),
            'products' => $products,
        ]);
    }

    #[Route('/product-show-{id}', name: 'app_show_product')]
    public function show(Product $product): Response
    {
        return $this->render('dashboard/Product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/edit-product-{id}', name: 'app_edit_product', methods: ['POST'])]
    public function edit(CategoryRepository     $categoryRepository,
                         ColorRepository        $colorRepository,
                         SizeRepository         $sizeRepository,
                         Request                $request,
                         EntityManagerInterface $em,
                         Product                $product): Response
    {

            $file = $request->files->get('productImage');
        if($file){
            $upload_dir_product = $this->getParameter('upload_dir_product');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_product,
                $filename
            );
            $product->setProductImage($filename);
        }
//        dd($request->files);

        $product
//            ->setProductImage()
            ->setProductName($request->get("nomProduct"))
            ->setProductPrice($request->get("nomPrice"))
            ->setProductDescription($request->get("nomDescription"))
            ->setCategory($categoryRepository->findOneById($request->get("selectCategory")));

        $product->getColor()->clear();
        foreach ($request->get("selectColor") as $color)
            $product->addColor($colorRepository->find($color));

        $product->getSize()->clear();
        foreach ($request->get("selectSize") as $size)
            $product->addSize($sizeRepository->find($size));

        $em->persist($product);
        $em->flush();
        return $this->redirectToRoute('app_product');
    }

    #[Route('/product-delete{id}', name: 'app_delete_product', methods: ['GET', 'POST'])]
    public function delete(EntityManagerInterface $em, Product $product): Response
    {
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute('app_product');
    }
}
