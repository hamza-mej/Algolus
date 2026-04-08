<?php

namespace App\Service;

/**
 * CDN Configuration Service
 * Abstracts CDN provider integration (Cloudinary, S3, etc.)
 */
class CDNConfigService
{
    private array $config;
    private string $provider;
    private string $baseUrl;

    public function __construct(array $cdnConfig = [])
    {
        $this->config = $cdnConfig;
        $this->provider = $cdnConfig['provider'] ?? 'local';
        $this->baseUrl = $cdnConfig['base_url'] ?? '/uploads';
    }

    /**
     * Get CDN URL for an image with optional transformations
     */
    public function getImageUrl(string $path, array $options = []): string
    {
        switch ($this->provider) {
            case 'cloudinary':
                return $this->getCloudinaryUrl($path, $options);
            case 's3':
                return $this->getS3Url($path, $options);
            case 'local':
            default:
                return $this->getLocalUrl($path, $options);
        }
    }

    /**
     * Get Cloudinary URL with transformations
     */
    private function getCloudinaryUrl(string $path, array $options = []): string
    {
        $cloudName = $this->config['cloudinary_cloud_name'] ?? '';
        if (!$cloudName) {
            return $this->getLocalUrl($path, $options);
        }

        $transformations = [];

        // Width
        if (isset($options['width'])) {
            $transformations[] = 'w_' . $options['width'];
        }

        // Height
        if (isset($options['height'])) {
            $transformations[] = 'h_' . $options['height'];
        }

        // Crop mode
        if (isset($options['crop'])) {
            $transformations[] = 'c_' . $options['crop'];
        }

        // Quality
        if (isset($options['quality'])) {
            $transformations[] = 'q_' . $options['quality'];
        }

        // Format (auto for WebP support)
        if ($options['format'] ?? false) {
            $transformations[] = 'f_' . $options['format'];
        } else {
            $transformations[] = 'f_auto';
        }

        $transformPath = !empty($transformations)
            ? implode(',', $transformations) . '/'
            : '';

        // Remove leading slashes and replace spaces
        $cleanPath = ltrim($path, '/');
        $cleanPath = str_replace(' ', '%20', $cleanPath);

        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$transformPath}{$cleanPath}";
    }

    /**
     * Get AWS S3 URL with CloudFront support
     */
    private function getS3Url(string $path, array $options = []): string
    {
        $region = $this->config['s3_region'] ?? 'us-east-1';
        $bucket = $this->config['s3_bucket'] ?? '';
        $cloudfront = $this->config['cloudfront_domain'] ?? '';

        if (!$bucket) {
            return $this->getLocalUrl($path, $options);
        }

        $cleanPath = ltrim($path, '/');

        if ($cloudfront) {
            return "https://{$cloudfront}/{$cleanPath}";
        }

        return "https://{$bucket}.s3.{$region}.amazonaws.com/{$cleanPath}";
    }

    /**
     * Get local URL (fallback)
     */
    private function getLocalUrl(string $path, array $options = []): string
    {
        $path = ltrim($path, '/');
        return $this->baseUrl . '/' . $path;
    }

    /**
     * Get CDN configuration for frontend
     */
    public function getClientConfig(): array
    {
        return [
            'provider' => $this->provider,
            'baseUrl' => $this->baseUrl,
            'options' => [
                'cloudinary' => [
                    'cloudName' => $this->config['cloudinary_cloud_name'] ?? '',
                    'uploadPreset' => $this->config['cloudinary_upload_preset'] ?? '',
                ],
                's3' => [
                    'bucket' => $this->config['s3_bucket'] ?? '',
                    'region' => $this->config['s3_region'] ?? '',
                ],
            ],
        ];
    }

    /**
     * Generate responsive image srcset
     */
    public function getResponsiveSrcset(string $path, array $sizes = [480, 768, 1024, 1440]): string
    {
        $srcset = [];

        foreach ($sizes as $size) {
            $url = $this->getImageUrl($path, [
                'width' => $size,
                'crop' => 'fill',
                'quality' => 85,
            ]);
            $srcset[] = "{$url} {$size}w";
        }

        return implode(', ', $srcset);
    }

    /**
     * Get picture element with CDN images and formats
     */
    public function getPictureElement(
        string $path,
        string $alt = '',
        array $sizes = [480, 768, 1024],
        string $classes = ''
    ): string {
        $srcset = $this->getResponsiveSrcset($path, $sizes);
        $fallback = $this->getImageUrl($path, ['width' => 800, 'crop' => 'fill']);

        return <<<HTML
<picture>
    <source srcset="$srcset" media="(min-width: 1024px)">
    <source srcset="{$this->getImageUrl($path, ['width' => 768, 'crop' => 'fill'])}" media="(min-width: 768px)">
    <source srcset="{$this->getImageUrl($path, ['width' => 480, 'crop' => 'fill'])}" media="(min-width: 480px)">
    <img src="$fallback" alt="$alt" class="$classes" loading="lazy">
</picture>
HTML;
    }

    /**
     * Generate thumbnail URL
     */
    public function getThumbnail(string $path, int $size = 200): string
    {
        return $this->getImageUrl($path, [
            'width' => $size,
            'height' => $size,
            'crop' => 'fill',
            'quality' => 80,
        ]);
    }

    /**
     * Generate multiple sizes at once for caching/preloading
     */
    public function generateImageVariants(string $path): array
    {
        return [
            'thumbnail' => $this->getThumbnail($path, 150),
            'small' => $this->getImageUrl($path, ['width' => 400, 'crop' => 'fill', 'quality' => 85]),
            'medium' => $this->getImageUrl($path, ['width' => 800, 'crop' => 'fill', 'quality' => 85]),
            'large' => $this->getImageUrl($path, ['width' => 1200, 'crop' => 'fill', 'quality' => 85]),
            'original' => $this->getImageUrl($path),
        ];
    }

    /**
     * Check if CDN is configured
     */
    public function isCDNEnabled(): bool
    {
        return $this->provider !== 'local';
    }

    /**
     * Get provider name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }
}
