<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\Cart\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationUserController extends AbstractController
{
    #[Route('/registerUser', name: 'app_register_user')]
    public function register(CartService $cartService, Request $request, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login_user');
        }

        return $this->render('Front/Login/Registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser()
        ]);
    }

    #[Route('/registeredit{id}', name: 'app_edit_register', methods: ['GET','POST'])]
    public function edit(Request $request,EntityManagerInterface $em,User $user): Response
    {
//        alert('edit');

        if($request->request){

            $user->setCountry($request->get("Country"));
        }

        $em->persist($user);
        $em->flush();
//        return $this->redirectToRoute('app_product');
    }
}

