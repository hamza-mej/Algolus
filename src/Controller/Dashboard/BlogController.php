<?php

namespace App\Controller\Dashboard;


use App\Entity\Blog;
use App\Form\BlogFormType;
use App\Repository\BlogRepository;
use App\Service\Cart\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{

    //    ******************         {{  Blog   }}          ******************


    #[Route('/dashboard_blog', name: 'dashboard_blog')]
    public function blog(BlogRepository $blogRepository, PaginatorInterface $paginator): Response
    {

        $blogs = $blogRepository->findAll();
//        $blogs = $paginator->paginate(
//            $data,
//            $request->query->getInt('page', 1),
//            3
//        );

        return $this->render('dashboard/Blog/index.html.twig', [
            'blogs' => $blogs,
        ]);
    }

    #[Route('/dashboard-create-blog', name: 'dashboard_create_blog')]
    public function create(BlogRepository         $blogRepository, Request $request,
                         EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {

        $data = $request->request->all();
        $blog = new Blog;

        $file = $request->files->get('BlogImage');
        if($file){
            $upload_dir_blog = $this->getParameter('upload_dir_blog');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_blog,
                $filename
            );
            $blog->setImage($filename);
        }

        $blog
//            ->setProductImage()
            ->setTitle($data["BlogTitle"])
            ->setDescription($data["BlogDescription"]);


            $em->persist($blog);
            $em->flush();

            return $this->redirectToRoute('dashboard_blog');

    }


    #[Route('/blog_edit{id}', name: 'dashboard_edit_blog', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, Blog $blog): Response
    {

        $file = $request->files->get('BlogImage');
        if($file){
            $upload_dir_blog= $this->getParameter('upload_dir_blog');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_blog,
                $filename
            );
            $blog->setImage($filename);
        }

        $blog->setTitle($request->get("BlogTitle"))
             ->setDescription($request->get("BlogDescription"));


        $em->persist($blog);
        $em->flush();
        return $this->redirectToRoute('dashboard_blog');
    }

    #[Route('/blog_delete{id}', name: 'dashboard_delete_blog', methods: ['GET', 'POST'])]
    public function delete(EntityManagerInterface $em, Blog $blog): Response
    {
        $em->remove($blog);
        $em->flush();

        return $this->redirectToRoute('dashboard_blog');
    }
}
