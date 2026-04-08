<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Wishlist;
use App\Repository\ProductRepository;
use App\Repository\WishlistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/wishlist', name: 'api_wishlist')]
class WishlistApiController extends AbstractController
{
    /**
     * Get user's wishlist
     */
    #[Route('', name: '_get', methods: ['GET'])]
    public function getWishlist(
        WishlistRepository $wishlistRepository,
        Request $request
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $page = max(1, (int)$request->query->get('page', 1));
        $items = $wishlistRepository->findByUser($user, $page, 20);
        $count = $wishlistRepository->countByUser($user);

        return $this->json([
            'success' => true,
            'count' => $count,
            'items' => array_map(fn($item) => [
                'id' => $item->getId(),
                'product' => [
                    'id' => $item->getProduct()->getId(),
                    'name' => $item->getProduct()->getProductName(),
                    'price' => $item->getProduct()->getProductPrice(),
                    'image' => $item->getProduct()->getProductImage(),
                    'url' => $this->generateUrl('app_product_show', ['id' => $item->getProduct()->getId()]),
                ],
                'addedAt' => $item->getCreatedAt()->format('Y-m-d'),
            ], $items),
            'pagination' => [
                'page' => $page,
                'total' => ceil($count / 20),
                'per_page' => 20,
            ],
        ]);
    }

    /**
     * Add product to wishlist
     */
    #[Route('/add', name: '_add', methods: ['POST'])]
    public function addToWishlist(
        Request $request,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;

        if (!$productId) {
            return $this->json(['error' => 'Product ID required'], Response::HTTP_BAD_REQUEST);
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if already in wishlist
        if ($wishlistRepository->isInWishlist($user, $product)) {
            return $this->json(['error' => 'Already in wishlist'], Response::HTTP_BAD_REQUEST);
        }

        $wishlist = new Wishlist();
        $wishlist->setUser($user)
            ->setProduct($product);

        $em->persist($wishlist);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => '❤️ Added to wishlist',
            'wishlistId' => $wishlist->getId(),
            'count' => $wishlistRepository->countByUser($user),
        ]);
    }

    /**
     * Remove from wishlist
     */
    #[Route('/remove', name: '_remove', methods: ['POST', 'DELETE'])]
    public function removeFromWishlist(
        Request $request,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;

        if (!$productId) {
            return $this->json(['error' => 'Product ID required'], Response::HTTP_BAD_REQUEST);
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $wishlist = $wishlistRepository->findWishlistItem($user, $product);
        if (!$wishlist) {
            return $this->json(['error' => 'Not in wishlist'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($wishlist);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => '💔 Removed from wishlist',
            'count' => $wishlistRepository->countByUser($user),
        ]);
    }

    /**
     * Check if product is in wishlist
     */
    #[Route('/check/{id}', name: '_check', methods: ['GET'])]
    public function checkWishlist(
        int $id,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $product = $productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $inWishlist = $wishlistRepository->isInWishlist($user, $product);

        return $this->json([
            'inWishlist' => $inWishlist,
            'count' => $wishlistRepository->countByUser($user),
        ]);
    }

    /**
     * Get wishlist count
     */
    #[Route('/count', name: '_count', methods: ['GET'])]
    public function getWishlistCount(WishlistRepository $wishlistRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        return $this->json([
            'count' => $wishlistRepository->countByUser($user),
        ]);
    }
}
