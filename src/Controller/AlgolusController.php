<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProductRepository;
use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


//#[Route('/algolus', name: 'app_')]
class AlgolusController extends AbstractController
{
    #[Route('/', name: 'app_algolus')]
    public function index(CartService $cartService, SessionInterface $session, ProductRepository $productRepository): Response
    {

        return $this->render('Front/base.html.twig', [
            'products' => $productRepository->findAll(),
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser()
        ]);
    }
}
