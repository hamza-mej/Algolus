<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Cart\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/cart', name: 'api_cart')]
class CartApiController extends AbstractController
{
    /**
     * Add product to cart via AJAX
     */
    #[Route('/add', name: '_add', methods: ['POST'])]
    public function add(
        Request $request,
        CartService $cartService,
        ProductRepository $productRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;
        
        if (!$productId) {
            return $this->json(['error' => 'Product ID required'], Response::HTTP_BAD_REQUEST);
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $cartService->add($product);

        return $this->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart' => $this->getCartData($cartService),
        ]);
    }

    /**
     * Remove product from cart via AJAX
     */
    #[Route('/remove', name: '_remove', methods: ['POST'])]
    public function remove(
        Request $request,
        CartService $cartService,
        ProductRepository $productRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;
        
        if (!$productId) {
            return $this->json(['error' => 'Product ID required'], Response::HTTP_BAD_REQUEST);
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $cartService->remove($product);

        return $this->json([
            'success' => true,
            'message' => 'Product removed from cart',
            'cart' => $this->getCartData($cartService),
        ]);
    }

    /**
     * Get current cart data
     */
    #[Route('/data', name: '_data', methods: ['GET'])]
    public function getData(CartService $cartService): JsonResponse
    {
        return $this->json($this->getCartData($cartService));
    }

    /**
     * Update product quantity in cart via AJAX
     */
    #[Route('/update', name: '_update', methods: ['POST', 'PATCH'])]
    public function update(
        Request $request,
        CartService $cartService,
        ProductRepository $productRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;
        $quantity = $data['quantity'] ?? 0;

        if (!$productId || $quantity < 0) {
            return $this->json(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        // Remove all and re-add with new quantity
        $cartService->remove($product);
        for ($i = 0; $i < $quantity; $i++) {
            $cartService->add($product);
        }

        return $this->json([
            'success' => true,
            'message' => 'Cart updated',
            'cart' => $this->getCartData($cartService),
        ]);
    }

    /**
     * Clear cart
     */
    #[Route('/clear', name: '_clear', methods: ['POST'])]
    public function clear(CartService $cartService): JsonResponse
    {
        $cartService->removeAll();

        return $this->json([
            'success' => true,
            'message' => 'Cart cleared',
            'cart' => $this->getCartData($cartService),
        ]);
    }

    /**
     * Helper to format cart data
     */
    private function getCartData(CartService $cartService): array
    {
        $fullCart = $cartService->getFullCart();
        
        return [
            'items' => array_map(fn($item) => [
                'id' => $item['product']?->getId(),
                'name' => $item['product']?->getProductName(),
                'price' => $item['product']?->getProductPrice(),
                'image' => $item['product']?->getProductImage(),
                'quantity' => $item['quantity'],
                'subtotal' => ($item['product']?->getProductPrice() ?? 0) * $item['quantity'],
            ], $fullCart),
            'total' => $cartService->getTotal(),
            'count' => count($fullCart),
        ];
    }
}
