<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class SEOService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Generate meta description
     */
    public function generateMetaDescription(string $text, int $maxLength = 160): string
    {
        $text = strip_tags($text);
        $text = trim($text);
        
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength - 3) . '...';
        }

        return htmlspecialchars($text);
    }

    /**
     * Generate SEO-friendly slug
     */
    public function generateSlug(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = trim($text, '-');
        return strtolower($text);
    }

    /**
     * Generate product structured data (JSON-LD)
     */
    public function generateProductSchema(Product $product, string $baseUrl): array
    {
        $reviews = $product->getReviews();
        $avgRating = 0;
        $reviewCount = 0;

        if (count($reviews) > 0) {
            $avgRating = array_sum(array_map(fn($r) => $r->getRating(), $reviews)) / count($reviews);
            $reviewCount = count($reviews);
        }

        return [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->getProductName(),
            'description' => $product->getProductDescription(),
            'image' => $baseUrl . $product->getProductImage(),
            'url' => $baseUrl . '/product/' . $product->getId(),
            'priceCurrency' => 'USD',
            'price' => $product->getProductPrice(),
            'availability' => 'https://schema.org/' . ($product->isOnSale() ? 'InStock' : 'OutOfStock'),
            'aggregateRating' => $reviewCount > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => round($avgRating, 1),
                'reviewCount' => $reviewCount,
            ] : null,
        ];
    }

    /**
     * Generate organization schema
     */
    public function generateOrganizationSchema(string $baseUrl): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Algolus',
            'url' => $baseUrl,
            'logo' => $baseUrl . '/logo.png',
            'sameAs' => [
                'https://facebook.com/algolus',
                'https://twitter.com/algolus',
                'https://instagram.com/algolus',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Customer Service',
                'email' => 'support@algolus.com',
            ],
        ];
    }

    /**
     * Generate breadcrumb schema
     */
    public function generateBreadcrumbSchema(array $items, string $baseUrl): array
    {
        $itemListElement = [];
        foreach ($items as $index => $item) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $baseUrl . $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement,
        ];
    }

    /**
     * Check SEO score for product
     */
    public function checkProductSEO(Product $product): array
    {
        $issues = [];
        $score = 100;

        // Check title
        $name = $product->getProductName();
        if (strlen($name) < 30 || strlen($name) > 60) {
            $issues[] = 'Product name should be 30-60 characters';
            $score -= 10;
        }

        // Check description
        $desc = $product->getProductDescription();
        if (strlen($desc) < 100) {
            $issues[] = 'Product description should be at least 100 characters';
            $score -= 10;
        }

        // Check image
        if (!$product->getProductImage()) {
            $issues[] = 'Product image is missing';
            $score -= 15;
        }

        // Check category
        if (!$product->getCategory()) {
            $issues[] = 'Product category is missing';
            $score -= 10;
        }

        // Check price
        if ($product->getProductPrice() <= 0) {
            $issues[] = 'Product price is invalid';
            $score -= 10;
        }

        return [
            'score' => max(0, $score),
            'issues' => $issues,
            'recommendations' => $this->getSEORecommendations($product),
        ];
    }

    /**
     * Get SEO recommendations
     */
    private function getSEORecommendations(Product $product): array
    {
        $recommendations = [];

        // Check for reviews
        if (count($product->getReviews()) === 0) {
            $recommendations[] = 'Encourage customers to leave reviews for better search rankings';
        }

        // Check for wishlist popularity
        $wishlistCount = count($product->getWishlists() ?? []);
        if ($wishlistCount === 0) {
            $recommendations[] = 'Promote product to increase wishlist saves';
        }

        // Check keywords in description
        $keywords = ['best', 'quality', 'premium', 'new', 'exclusive'];
        $desc = strtolower($product->getProductDescription());
        $hasKeywords = false;
        foreach ($keywords as $keyword) {
            if (strpos($desc, $keyword) !== false) {
                $hasKeywords = true;
                break;
            }
        }
        if (!$hasKeywords) {
            $recommendations[] = 'Add descriptive keywords to product description';
        }

        return $recommendations;
    }

    /**
     * Generate XML sitemap
     */
    public function generateXMLSitemap(string $baseUrl): string
    {
        $products = $this->em->getRepository(Product::class)->findAll();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Add static pages
        $staticPages = [
            ['loc' => '/', 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => '/products', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => '/blog', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ];

        foreach ($staticPages as $page) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $baseUrl . $page['loc'] . '</loc>' . "\n";
            $xml .= '    <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $page['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Add products
        foreach ($products as $product) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $baseUrl . '/product/' . $product->getId() . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $product->getUpdatedAt()->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate robots.txt
     */
    public function generateRobotsTxt(): string
    {
        return <<<TXT
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/
Disallow: /*.json
Disallow: /*?*sort=
Disallow: /*?*page=

Sitemap: /sitemap.xml
TXT;
    }
}
