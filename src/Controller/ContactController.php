<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\PersonalInfo;
use App\Entity\Product;
use App\Form\ContactFormType;
use App\Form\ProductFormType;
use App\Repository\PersonalInfoRepository;
use App\Service\Cart\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(CartService $cartService, Request $request,
                            EntityManagerInterface $em, PersonalInfoRepository $personalInfoRepository): Response
    {

        $contact = new Contact;
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($contact);
            $em->flush();

            return $this->redirectToRoute('app_contact');
        }

        $PersonalInfo = $personalInfoRepository->findAll();

        return $this->render('Front/Contact/index.html.twig', [
            'controller_name' => 'ContactController',
            'ContactForm' => $form->createView(),
            'items' => $cartService->getFullCart(),
            'total' => $cartService->getTotal(),
            'user' => $this->getUser(),
            'PersonalInfo' => $PersonalInfo,

        ]);
    }
}
