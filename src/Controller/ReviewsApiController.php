<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/reviews', name: 'api_reviews')]
class ReviewsApiController extends AbstractController
{
    /**
     * Get product reviews
     */
    #[Route('/{id}', name: '_get', methods: ['GET'])]
    public function getReviews(
        int $id,
        ProductRepository $productRepository,
        ReviewRepository $reviewRepository,
        Request $request
    ): JsonResponse {
        $product = $productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $page = max(1, (int)$request->query->get('page', 1));
        $reviews = $reviewRepository->findApprovedByProduct($product, $page, 10);
        $count = $reviewRepository->countApprovedByProduct($product);
        $average = $reviewRepository->getAverageRating($product);
        $distribution = $reviewRepository->getRatingDistribution($product);

        return $this->json([
            'success' => true,
            'reviews' => array_map(fn($review) => [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'title' => $review->getTitle(),
                'comment' => $review->getComment(),
                'author' => $review->getUser()->getEmail(),
                'createdAt' => $review->getCreatedAt()->format('Y-m-d H:i'),
                'helpful' => $review->getHelpfulCount(),
            ], $reviews),
            'stats' => [
                'average' => $average,
                'count' => $count,
                'distribution' => $distribution,
            ],
            'pagination' => [
                'page' => $page,
                'total' => ceil($count / 10),
                'per_page' => 10,
            ],
        ]);
    }

    /**
     * Submit a review
     */
    #[Route('/submit', name: '_submit', methods: ['POST'])]
    public function submitReview(
        Request $request,
        ProductRepository $productRepository,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $data = json_decode($request->getContent(), true);
        $productId = $data['productId'] ?? null;
        $rating = $data['rating'] ?? null;
        $title = $data['title'] ?? null;
        $comment = $data['comment'] ?? null;

        if (!$productId || !$rating || !$title || !$comment) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();

        // Check if user already reviewed
        $existing = $reviewRepository->findUserReview($user, $product);
        if ($existing && !$data['isEdit']) {
            return $this->json(['error' => 'You have already reviewed this product'], Response::HTTP_BAD_REQUEST);
        }

        $review = $existing ?? new Review();
        $review->setProduct($product)
            ->setUser($user)
            ->setRating((int)$rating)
            ->setTitle($title)
            ->setComment($comment)
            ->setStatus('pending');

        if ($existing) {
            $review->setUpdatedAt(new \DateTimeImmutable());
        }

        $errors = $validator->validate($review);
        if (count($errors) > 0) {
            return $this->json([
                'error' => 'Validation failed',
                'errors' => array_map(fn($e) => $e->getMessage(), (array)$errors),
            ], Response::HTTP_BAD_REQUEST);
        }

        $em->persist($review);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => $existing ? 'Review updated. Pending approval.' : 'Review submitted. Pending approval.',
            'reviewId' => $review->getId(),
        ]);
    }

    /**
     * Mark review as helpful
     */
    #[Route('/{id}/helpful', name: '_helpful', methods: ['POST'])]
    public function markHelpful(
        Review $review,
        EntityManagerInterface $em
    ): JsonResponse {
        $review->incrementHelpful();
        $em->flush();

        return $this->json([
            'success' => true,
            'helpful' => $review->getHelpfulCount(),
        ]);
    }
}
