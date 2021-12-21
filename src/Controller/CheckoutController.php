<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\EditRegistrationFormType;
use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function index(CartService $cartService, Request $request): Response
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_login_user');
        }

        $user = $this->getUser();
        $form = $this->createForm(EditRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

//            return $this->redirectToRoute('app_login_user');
        }

        return $this->render('Front/checkout/index.html.twig', [
            'EditRegistrationForm' => $form->createView(),
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/checkoutRemove{id}', name: 'app_checkout_remove')]
    public function remove(Product $product, CartService $cartService ): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $cartService->remove($product);

        return $this->redirectToRoute('app_checkout');
    }

}
