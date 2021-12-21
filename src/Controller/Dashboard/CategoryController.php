<?php

namespace App\Controller\Dashboard;

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
    public function index(CategoryRepository     $categoryRepository, Request $request,
                           PaginatorInterface $paginator): Response
    {
        $data = $categoryRepository->findAll();
        $categories = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            3
        );

        return $this->render('dashboard/Category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/create-category', name: 'app_create_category')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {

        $category = new Category;

        $file = $request->files->get('categoryImage');
        if($file){
            $upload_dir_category = $this->getParameter('upload_dir_category');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_category,
                $filename
            );
            $category->setCategoryImage($filename);
        }
        $category->setCategoryName($request->get("nomCategory"));

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute('app_category');


    }

    #[Route('/create-category-list', name: 'app_create_category_list')]
    public function createList(CategoryRepository     $categoryRepository, Request $request,
                               EntityManagerInterface $em, PaginatorInterface $paginator): Response
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

            return $this->redirectToRoute('app_category_list');
        }
        return $this->render('dashboard/Category/list.html.twig', [
            'CreateForm' => $form->createView(),
            'categories' => $categories,
        ]);
    }


    #[Route('/edit-category-{id}', name: 'app_edit_category', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, Category $category): Response
    {

        $file = $request->files->get('categoryImage');
        if($file){
            $upload_dir_category = $this->getParameter('upload_dir_category');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_category,
                $filename
            );
            $category->setCategoryImage($filename);
        }
        $category->setCategoryName($request->get("nomCategory"));

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute('app_category');
    }

    #[Route('/delete-category-{id}', name: 'app_delete_category', methods: ['GET', 'POST'])]
    public function delete(EntityManagerInterface $em, Category $category): Response
    {
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('app_category');
    }
}

