<?php

namespace App\Controller;

use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityUserController extends AbstractController
{

    #[Route('/loginUser', name: 'app_login_user')]
    public function loginUser(CartService $cartService, AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('Front/Login/Security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser()
        ]);
    }

    #[Route('/logoutUser', name: 'app_logout_user')]
    public function logoutUser()
    {
        return $this->redirectToRoute('app_login_user');
//        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

}

