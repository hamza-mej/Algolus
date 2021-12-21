<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Product;
use App\Form\SearchForm;
use App\Repository\BannerRepository;
use App\Repository\HomeBlogRepository;
use App\Repository\ProductRepository;
use App\Repository\SecondBannerRepository;
use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


//#[Route('/algolus', name: 'app_')]
class ProductController extends AbstractController
{

    #[Route('/', name: 'app_algolus')]
    public function index(CartService $cartService, ProductRepository $productRepository, HomeBlogRepository $homeBlogRepository,BannerRepository $bannerRepository,SecondBannerRepository $secondBannerRepository): Response
    {

        $products = $productRepository->findAll();
        $homeBlog = $homeBlogRepository->findAll();
        $banner = $bannerRepository->findAll();
        $secondBanner = $secondBannerRepository->findAll();
        return $this->render('Front/index.html.twig', [
            'products' => $products,
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
            'HomeBlog' => $homeBlog,
            'banner' => $banner,
            'secondBanner' => $secondBanner,

        ]);
    }

    #[Route('/miniCartRemove{id}', name: 'app_miniCart_remove')]
    public function remove(Product $product, SessionInterface $session )
    {
        $panier = $session->get('panier', []);

        if (!empty($panier[$product->getId()])){
            unset($panier[$product->getId()]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('app_algolus');
    }

    #[Route('/shop', name: 'app_product_shop')]

    public function shop(CartService $cartService, ProductRepository $productRepository, Request $request,$maxItemPerPage=2,
                         ): Response
    {

//        $data = $productRepository->findAll();
//        $products = $paginator->paginate(
//            $data,
//            $request->query->getInt('page', 1),
//            2
//        );
        $product = new Product();

        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);
        [$min , $max] = $productRepository->findMinMax($data);
        $products = $productRepository->findSearch($data, $maxItemPerPage=20);
        if ($request->get('ajax')){
            return new JsonResponse([
                'content' => $this->renderView('Front/Product/_product.html.twig', ['products' => $products]),
                'contentShow' => $this->renderView('Front/Product/_product_show.html.twig', ['products' => $products]),
                'sorting' => $this->renderView('Front/Product/_sorting.html.twig', ['products' => $products]),
                'pagination' => $this->renderView('Front/Product/_pagination.html.twig', ['products' => $products]),
                'min' => $min,
                'max' => $max,
            ]);
        }



        return $this->render('Front/Product/index.html.twig', [
            'products' => $products,
            'product' => $product,
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
            'min' => $min,
            'max' => $max,
            'formFilter' => $form->createView(),
        ]);
    }

    #[Route('/shopModal{id}', name: 'app_shop_modal')]
    public function modal(Product $product): Response
    {
        return $this->render('Front/Product/modal.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/shopShow{id}', name: 'app_shop_show')]
    public function show(CartService $cartService, Product $product): Response
    {

        return $this->render('Front/Product/productDetails.html.twig', [
            'product' => $product,
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
        ]);
    }
}
