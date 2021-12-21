<?php

namespace App\Controller\Dashboard;

use App\Entity\AboutUs;
use App\Entity\Banner;
use App\Entity\Contact;
use App\Entity\HomeBlog;
use App\Entity\PersonalInfo;
use App\Entity\SecondBanner;
use App\Entity\User;
use App\Repository\AboutUsRepository;
use App\Repository\BannerRepository;
use App\Repository\ContactRepository;
use App\Repository\HomeBlogRepository;
use App\Repository\PersonalInfoRepository;
use App\Repository\SecondBannerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    //    ******************         {{  Users  }}          ******************

    #[Route('/users', name: 'app_users')]
    public function create(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('dashboard/Users/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/edit-user{id}', name: 'app_edit_user')]
    public function EditUser(Request $request, User $user, EntityManagerInterface $em): Response
    {


        $user
             ->setEmail($request->get("emailUser"))
             ->setFirstName($request->get("firstName"))
             ->setLastName($request->get("lastName"));

        $users = array($request->get("selectRoles"));
        $user->setRoles($users);

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_users');
    }

    //    ******************         {{  Contact Us Received  }}          ******************

    #[Route('/dashboard-contact-us', name: 'dashboard_contactUs')]
    public function contactUsReceived(ContactRepository $contactRepository): Response
    {
        $contact = $contactRepository->findAll();
        return $this->render('dashboard/ContactUs/index.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/delete-contact-us{id}', name: 'dashboard_delete_contactUs', methods: ['GET', 'POST'])]
    public function delete(EntityManagerInterface $em, Contact $contact): Response
    {
        $em->remove($contact);
        $em->flush();
        return $this->redirectToRoute('dashboard_contactUs');
    }


    //    ******************         {{  Personal Info }}          ******************

    #[Route('/dashboard-personal-info', name: 'dashboard_personalInfo')]
    public function contactUsInfo(PersonalInfoRepository $personalInfoRepository): Response
    {
        $personalInfo = $personalInfoRepository->findAll();
        return $this->render('dashboard/ContactUs/PersonalInfo/index.html.twig', [
            'personalInfo' => $personalInfo,
        ]);
    }

    #[Route('/edit-personal-info{id}', name: 'dashboard_edit_personalInfo')]
    public function EditPersonalInfos(PersonalInfo $personalInfo, Request $request, EntityManagerInterface $em): Response
    {
        $personalInfo
            ->setEmail($request->get("PersonalInfoEmail"))
            ->setAddress($request->get("PersonalInfoAddress"))
            ->setPhone($request->get("PersonalInfoPhone"))
            ->setContent($request->get("PersonalInfoContent"));

        $em->persist($personalInfo);
        $em->flush();

        return $this->redirectToRoute('dashboard_personalInfo');
    }

    //    ******************         {{  About Us  }}          ******************

    #[Route('/dashboard-about-us', name: 'dashboard_aboutUs')]
    public function aboutUs(AboutUsRepository $aboutUsRepository): Response
    {
        $aboutUs = $aboutUsRepository->findAll();
        return $this->render('dashboard/AboutUs/index.html.twig', [
            'aboutUs' => $aboutUs,
        ]);
    }

    #[Route('/edit-aboutUs{id}', name: 'dashboard_edit_about_us')]
    public function EditAboutUs(Request $request, AboutUs $aboutUs, EntityManagerInterface $em): Response
    {

        $file = $request->files->get('AboutUsImage');
        if($file){
            $upload_dir_aboutUs= $this->getParameter('upload_dir_aboutUs');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_aboutUs,
                $filename
            );
            $aboutUs->setImage($filename);
        }

        $aboutUs
                ->setTitle($request->get("AboutUsTitle"))
                ->setDescription($request->get("AboutUsDescription"))
                ->setContent($request->get("AboutUsContent"));

//            $category = $categoryRepository->findOneById($request->get("selectCategory"));
//            $product->setCategory($category);

        $em->persist($aboutUs);
        $em->flush();
        return $this->redirectToRoute('dashboard_aboutUs');
    }

    //    ******************         {{  Home Blog   }}          ******************

    #[Route('/dashboard-home-blog', name: 'dashboard_homeBlog')]
    public function homeBlog(HomeBlogRepository $homeBlogRepository): Response
    {
        $homeBlog = $homeBlogRepository->findAll();
        return $this->render('dashboard/AboutUs/HomeBlog/index.html.twig', [
            'homeBlog' => $homeBlog,
        ]);
    }

    #[Route('/edit-home-blog{id}', name: 'dashboard_edit_homeBlog')]
    public function EditHomeBlog(Request $request, HomeBlog $homeBlog, EntityManagerInterface $em): Response
    {

        $file = $request->files->get('HomeBlogImage');
        if($file){
            $upload_dir_homeBlog= $this->getParameter('upload_dir_homeBlog');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_homeBlog,
                $filename
            );
            $homeBlog->setHomeImage($filename);
        }


        $homeBlog->setHomeTitle($request->get("HomeBlogTitle"))
                 ->setHomeDescription($request->get("HomeBlogDescription"))
                 ->setHomeContent($request->get("HomeBlogContent"));

        $em->persist($homeBlog);
        $em->flush();
        return $this->redirectToRoute('dashboard_homeBlog');
    }

    //    ******************         {{  Banner }}          ******************

    #[Route('/dashboard-banner', name: 'dashboard_banner')]
    public function banner(BannerRepository $bannerRepository): Response
    {
        $banner = $bannerRepository->findAll();
        return $this->render('dashboard/Banner/index.html.twig', [
            'banner' => $banner,
        ]);
    }

    #[Route('/edit-banner{id}', name: 'dashboard_edit_banner')]
    public function EditBanner(Request $request, Banner $banner, EntityManagerInterface $em): Response
    {
        $file = $request->files->get('bannerImage');
        if($file){
            $upload_dir_banner= $this->getParameter('upload_dir_banner');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_banner,
                $filename
            );
            $banner->setImage($filename);
        }

        $banner->setSupTitle($request->get("BannerSupTitle"))
               ->setTitle($request->get("BannerTitle"))
               ->setDescription($request->get("BannerDescription"));


        $em->persist($banner);
        $em->flush();
        return $this->redirectToRoute('dashboard_banner');
    }

    //    ******************         {{  Second Banner  }}          ******************

    #[Route('/dashboard-secondBanner', name: 'dashboard_secondBanner')]
    public function secondBanner(SecondBannerRepository $secondBannerRepository): Response
    {
        $secondBanner = $secondBannerRepository->findAll();
        return $this->render('dashboard/Banner/SecondBanner/index.html.twig', [
            'secondBanner' => $secondBanner,
        ]);
    }

    #[Route('/edit-second-banner{id}', name: 'dashboard_edit_secondBanner')]
    public function EditSecondBanner(Request $request, SecondBanner $secondBanner, EntityManagerInterface $em): Response
    {
        $file = $request->files->get('SecondBannerImage');
        if($file){
            $upload_dir_secondBanner= $this->getParameter('upload_dir_secondBanner');
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move(
                $upload_dir_secondBanner,
                $filename
            );
            $secondBanner->setImage($filename);
        }

        $secondBanner->setTitle($request->get("SecondBannerTitle"));
//            $secondBanner->setDescription($request->get("SecondBannerDescription"));


        $em->persist($secondBanner);
        $em->flush();
        return $this->redirectToRoute('dashboard_secondBanner');
    }


}
