<?php

namespace App\Service\Cart;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {

    protected $requestStack;
    protected $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository){
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    protected function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    public function add(Product $product){
        $session = $this->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$product->getId()])){
            $panier[$product->getId()]++;
        } else {
            $panier[$product->getId()] = 1;
        }

        $session->set('panier', $panier);
    }

    public function remove(Product $product){

        $session = $this->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$product->getId()])){
            unset($panier[$product->getId()]);
        }

        $session->set('panier', $panier);
    }

    public function removeAll(){

        $this->getSession()->clear();
    }

    public function getFullCart() : array{

        $panier = $this->getSession()->get('panier', []);

        $panierWithData = [];

        foreach ( $panier as $id => $quantity){
            $panierWithData[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity
            ];
        }


        return $panierWithData;
    }

    public function getFullName() : string{

        $name = '';

        $panierWithData = $this->getFullCart();

        foreach ( $panierWithData as $item ){
            if ($item['product'] != null){
                $productName =   $item['product']->getProductName();
                $name .= ' '.$productName;
            }

        }
        return $name;
    }


    public function getTotal() : float{

        $total = 0;
        $panierWithData = $this->getFullCart();

        foreach ( $panierWithData as $item ){
            if ($item['product'] != null){
                $totalItem = $item['product']->getProductPrice() * $item['quantity'];
                $total += $totalItem;
            }

        }

        return $total;
    }


    //    public function getQuantity() : float{
//
//        $panierWithData = $this->getFullCart();
//
//        foreach ( $panierWithData as $item ){
//            if ($item['quantity'] != null){
//                $quantity =  $item['quantity'];
//            }
//
//        }
//        return $quantity;
//    }
}