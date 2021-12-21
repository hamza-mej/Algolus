<?php

namespace App\Service\Cart;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {

    protected $session;
    protected $productRepository;

    public function __construct(SessionInterface $session,ProductRepository $productRepository){
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    public function add(Product $product){
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$product->getId()])){
            $panier[$product->getId()]++;
        } else {
            $panier[$product->getId()] = 1;
        }

        $this->session->set('panier', $panier);
    }

    public function remove(Product $product){

        $panier = $this->session->get('panier', []);

        if (!empty($panier[$product->getId()])){
            unset($panier[$product->getId()]);
        }

        $this->session->set('panier', $panier);
    }

    public function removeAll(){

        $this->session->clear();
    }

    public function getFullCart() : array{

        $panier = $this->session->get('panier', []);

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