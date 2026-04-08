<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products', name: 'api_products')]
class ProductsApiController extends AbstractController
{
    /**
     * Get filtered products with AJAX
     */
    #[Route('/filter', name: '_filter', methods: ['GET'])]
    public function filter(
        Request $request,
        ProductRepository $productRepository
    ): JsonResponse {
        $search = new SearchData();
        $search->page = (int)$request->query->get('page', 1);
        $search->q = $request->query->get('q', '');
        $search->min = $request->query->get('min', null);
        $search->max = $request->query->get('max', null);
        $search->categories = array_filter(array_map('intval', explode(',', $request->query->get('categories', ''))));
        $search->color = array_filter(array_map('intval', explode(',', $request->query->get('colors', ''))));
        $search->size = array_filter(array_map('intval', explode(',', $request->query->get('sizes', ''))));
        $search->onSale = $request->query->getBoolean('onSale', false);

        $products = $productRepository->findSearch($search);

        return $this->json([
            'success' => true,
            'products' => array_map(fn($product) => [
                'id' => $product->getId(),
                'name' => $product->getProductName(),
                'price' => $product->getProductPrice(),
                'image' => $product->getProductImage(),
                'description' => substr($product->getProductDescription() ?? '', 0, 150) . '...',
                'onSale' => $product->isOnSale(),
            ], $products->getItems()),
            'pagination' => [
                'page' => $products->getCurrentPageNumber(),
                'pages' => ceil($products->getTotalItemCount() / $products->getItemNumberPerPage()),
                'total' => $products->getTotalItemCount(),
                'per_page' => $products->getItemNumberPerPage(),
            ],
        ]);
    }

    /**
     * Get price range for filtering
     */
    #[Route('/price-range', name: '_price_range', methods: ['GET'])]
    public function getPriceRange(ProductRepository $productRepository): JsonResponse
    {
        $search = new SearchData();
        [$min, $max] = $productRepository->findMinMax($search);

        return $this->json([
            'min' => $min,
            'max' => $max,
        ]);
    }

    /**
     * Search products autocomplete
     */
    #[Route('/search', name: '_search', methods: ['GET'])]
    public function search(
        Request $request,
        ProductRepository $productRepository
    ): JsonResponse {
        $query = $request->query->get('q', '');
        
        if (strlen($query) < 2) {
            return $this->json(['results' => []]);
        }

        $search = new SearchData();
        $search->q = $query;
        $search->page = 1;

        $products = $productRepository->findSearch($search, 5);

        return $this->json([
            'results' => array_map(fn($product) => [
                'id' => $product->getId(),
                'name' => $product->getProductName(),
                'price' => $product->getProductPrice(),
                'image' => $product->getProductImage(),
                'url' => $this->generateUrl('app_product_show', ['id' => $product->getId()]),
            ], $products->getItems()),
        ]);
    }
}
