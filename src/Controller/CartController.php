<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart_index')]
    public function index(CartService $cartService): Response
    {

        return $this->render('Front/Cart/index.html.twig', [
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/cartAdd{id}', name: 'app_cart_add')]
    public function add(Product $product, CartService $cartService ): Response
    {
        $cartService->add($product);

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/cartItem{id}', name: 'app_fav_add')]
    public function addFav(Product $product, CartService $cartService ): Response
    {
        $cartService->add($product);

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/cartRemove{id}', name: 'app_cart_remove')]
    public function remove(Product $product, CartService $cartService )
    {
        $cartService->remove($product);

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/Remove', name: 'app_cart_removeAll')]
    public function removeAll(CartService $cartService )
    {
        $cartService->removeAll();

        return $this->redirectToRoute('app_cart_index');
    }
}
