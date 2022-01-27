<?php

namespace App\Controller\Dashboard;

use App\Entity\Details;
use App\Repository\ColorRepository;
use App\Repository\DetailsRepository;
use App\Repository\ProductRepository;
use App\Repository\SizeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    #[Route('/dashboard-stock', name: 'app_stock')]
    public function index(ProductRepository $productRepository,ColorRepository $colorRepository, SizeRepository $sizeRepository, DetailsRepository $detailsRepository, ): Response
    {
        $color = $colorRepository->findAll();
        $size = $sizeRepository->findAll();
        $products = $productRepository->findAll();
        $details = $detailsRepository->findAll();

        return $this->render('dashboard/Stock/index.html.twig', [
            'controller_name' => 'StockController',
            'details' => $details,
            'color' => $color,
            'size' => $size,
            'products' => $products,
        ]);
    }

    #[Route('/create-stock', name: 'dashboard_create_stock')]
    public function create(ProductRepository $productRepository, Request $request, EntityManagerInterface $em): Response
    {

        if($request->isXmlHttpRequest()){

            $s = $request->getContent();
            $param = json_decode($s);


            foreach ($param as $item) {

                $details = new Details();

                $details->setColor($item->color)
                    ->setSize($item->size)
                    ->setQty($item->qty)
                    ->setProduct($productRepository->findOneById($item->ProductSelected));

                $em->persist($details);
                $em->flush();
            }


        }
        return $this->redirectToRoute('app_stock');
    }
}
