<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Blog;
use App\Form\SearchBlogForm;
use App\Form\SearchForm;
use App\Repository\AboutUsRepository;
use App\Repository\BlogRepository;
use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{

    //    ******************         {{  Blog   }}          ******************


    #[Route('/blog', name: 'app_blog')]
    public function blog(CartService $cartService, BlogRepository $blogRepository, Request $request): Response
    {



        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchBlogForm::class, $data);
        $form->handleRequest($request);
        $blogs = $blogRepository->findSearch($data);
        if ($request->get('ajax')){
            return new JsonResponse([
                'content' => $this->renderView('Front/Blog/_blog.html.twig', ['blogs' => $blogs]),
                'pagination' => $this->renderView('Front/blog/_pagination.html.twig', ['$blogs' => $blogs]),
            ]);
        }
//        $blogs = $blogRepository->findAll();
        return $this->render('Front/Blog/index.html.twig', [
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
            'blogs' => $blogs,
            'formFilter' => $form->createView(),
        ]);
    }

    #[Route('/blog_details{id}', name: 'app_blog_details')]
    public function blogDetails(CartService $cartService, Blog $blog): Response
    {

        return $this->render('Front/Blog/blogDetails.html.twig', [
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
            'blog' => $blog,

        ]);
    }

    //    ******************         {{  About Us   }}          ******************

    #[Route('/about_us', name: 'app_aboutUs')]
    public function aboutUs(CartService $cartService,AboutUsRepository $aboutUsRepository): Response
    {

        $aboutUs = $aboutUsRepository->findAll();
        return $this->render('Front/AboutUs/index.html.twig', [
            'controller_name' => 'BlogController',
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
            'aboutUs' => $aboutUs,
        ]);
    }

    //    ******************         {{  Shipping Policy  }}          ******************

    #[Route('/shipping_policy', name: 'app_shippingPolicy')]
    public function shippingPolicy(CartService $cartService): Response
    {

        return $this->render('Front/ShippingPolicy/index.html.twig', [
            'controller_name' => 'BlogController',
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
        ]);
    }

    //    ******************         {{  My Account  }}          ******************

    #[Route('/my_account', name: 'app_myAccount')]
    public function myAccount(CartService $cartService): Response
    {

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login_user');
        }

        return $this->render('Front/MyAccount/index.html.twig', [
            'controller_name' => 'BlogController',
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser()
        ]);
    }
}
