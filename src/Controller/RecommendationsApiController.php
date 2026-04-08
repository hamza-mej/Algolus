<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\RecommendationEngine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/recommendations', name: 'api_recommendations')]
class RecommendationsApiController extends AbstractController
{
    /**
     * Get personalized recommendations for logged-in user
     */
    #[Route('', name: '_get', methods: ['GET'])]
    public function getRecommendations(
        RecommendationEngine $engine,
        Request $request
    ): JsonResponse {
        $limit = max(1, min(20, (int)$request->query->get('limit', 6)));

        if ($this->getUser()) {
            $recommendations = $engine->getRecommendationsForUser($this->getUser(), $limit);
        } else {
            // Anonymous users get popular products
            $recommendations = [];
        }

        return $this->json([
            'success' => true,
            'recommendations' => array_map(fn($p) => [
                'id' => $p->getId(),
                'name' => $p->getProductName(),
                'price' => $p->getProductPrice(),
                'image' => $p->getProductImage(),
                'url' => $this->generateUrl('app_product_show', ['id' => $p->getId()]),
                'category' => $p->getCategory()?->getCategoryName(),
                'onSale' => $p->isOnSale(),
            ], $recommendations),
        ]);
    }

    /**
     * Get similar products for a specific product
     */
    #[Route('/similar/{id}', name: '_similar', methods: ['GET'])]
    public function getSimilarProducts(
        int $id,
        ProductRepository $productRepo,
        RecommendationEngine $engine,
        Request $request
    ): JsonResponse {
        $product = $productRepo->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $limit = max(1, min(20, (int)$request->query->get('limit', 6)));
        $similar = $engine->getSimilarProducts($product, $limit);

        return $this->json([
            'success' => true,
            'similar' => array_map(fn($p) => [
                'id' => $p->getId(),
                'name' => $p->getProductName(),
                'price' => $p->getProductPrice(),
                'image' => $p->getProductImage(),
                'url' => $this->generateUrl('app_product_show', ['id' => $p->getId()]),
            ], $similar),
        ]);
    }

    /**
     * Get "Customers also viewed" products
     */
    #[Route('/also-viewed/{id}', name: '_also_viewed', methods: ['GET'])]
    public function getAlsoViewedProducts(
        int $id,
        ProductRepository $productRepo,
        RecommendationEngine $engine,
        Request $request
    ): JsonResponse {
        $product = $productRepo->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $limit = max(1, min(20, (int)$request->query->get('limit', 4)));
        $alsoViewed = $engine->getAlsoViewedProducts($product, $limit);

        return $this->json([
            'success' => true,
            'also_viewed' => array_map(fn($p) => [
                'id' => $p->getId(),
                'name' => $p->getProductName(),
                'price' => $p->getProductPrice(),
                'image' => $p->getProductImage(),
                'url' => $this->generateUrl('app_product_show', ['id' => $p->getId()]),
            ], $alsoViewed),
        ]);
    }

    /**
     * Get related products
     */
    #[Route('/related/{id}', name: '_related', methods: ['GET'])]
    public function getRelatedProducts(
        int $id,
        ProductRepository $productRepo,
        RecommendationEngine $engine,
        Request $request
    ): JsonResponse {
        $product = $productRepo->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $limit = max(1, min(20, (int)$request->query->get('limit', 4)));
        $related = $engine->getRelatedProducts($product, $limit);

        return $this->json([
            'success' => true,
            'related' => array_map(fn($p) => [
                'id' => $p->getId(),
                'name' => $p->getProductName(),
                'price' => $p->getProductPrice(),
                'image' => $p->getProductImage(),
                'url' => $this->generateUrl('app_product_show', ['id' => $p->getId()]),
            ], $related),
        ]);
    }
}
