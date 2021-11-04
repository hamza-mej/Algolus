<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function create(CategoryRepository $categoryRepository, Request $request,
                           EntityManagerInterface $em,PaginatorInterface $paginator): Response
    {
        $data = $categoryRepository->findAll();
        $categories = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            3
        );

        $category = new Category;
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('app_category');
        }
        return $this->render('dashboard/Category/index.html.twig', [
            'CreateForm' => $form->createView(),
            'categories' => $categories,
        ]);
    }

    #[Route('/categoryshow{id}', name: 'app_show_category')]
    public function show(Category $category): Response
    {

        return $this->render('dashboard/Category/show.html.twig', [
            'category' => $category,
        ]);
    }


    #[Route('/categoryedit{id}', name: 'app_edit_category', methods: ['GET','POST'])]
    public function edit(Request $request,EntityManagerInterface $em,Category $category): Response
    {

        if($request->request){
            $category->setCategoryName($request->get("nomCategory"));
            $category->setCategoryDescription($request->get("nomDescription"));

        }
        $em->flush();
        return $this->redirectToRoute('app_category');
    }

    #[Route('/categorydelete{id}', name: 'app_delete_category', methods: ['GET','POST'])]
    public function delete(EntityManagerInterface $em, Category $category): Response
    {
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('app_category');
    }
}
