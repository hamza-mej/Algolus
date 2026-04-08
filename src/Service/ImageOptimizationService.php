<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageOptimizationService
{
    private string $uploadDir;
    private array $supportedFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private int $maxWidth = 2000;
    private int $maxHeight = 2000;
    private int $jpegQuality = 85;
    private int $webpQuality = 80;

    public function __construct(string $projectDir)
    {
        $this->uploadDir = $projectDir . '/public/uploads';
    }

    /**
     * Optimize uploaded image and generate multiple sizes
     * Returns array with paths to different versions
     */
    public function optimizeImage(UploadedFile $file, string $category = 'products'): array
    {
        if (!$this->isValidImage($file)) {
            throw new \InvalidArgumentException('Invalid image file');
        }

        $filename = $this->generateFilename($file);
        $categoryPath = $this->uploadDir . '/' . $category;

        if (!is_dir($categoryPath)) {
            mkdir($categoryPath, 0755, true);
        }

        // Load image
        $imagePath = $file->getPathname();
        $image = $this->loadImage($imagePath);

        if (!$image) {
            throw new \RuntimeException('Failed to load image');
        }

        $sizes = [];

        // Generate thumbnail (300px)
        $thumbnailPath = $categoryPath . '/thumb_' . $filename . '.jpg';
        $this->resizeAndSave($image, $thumbnailPath, 300, 300, true);
        $sizes['thumbnail'] = '/uploads/' . $category . '/thumb_' . $filename . '.jpg';

        // Generate small (500px)
        $smallPath = $categoryPath . '/small_' . $filename . '.jpg';
        $this->resizeAndSave($image, $smallPath, 500, 500, false);
        $sizes['small'] = '/uploads/' . $category . '/small_' . $filename . '.jpg';

        // Generate medium (800px)
        $mediumPath = $categoryPath . '/medium_' . $filename . '.jpg';
        $this->resizeAndSave($image, $mediumPath, 800, 800, false);
        $sizes['medium'] = '/uploads/' . $category . '/medium_' . $filename . '.jpg';

        // Generate large (1200px)
        $largePath = $categoryPath . '/large_' . $filename . '.jpg';
        $this->resizeAndSave($image, $largePath, 1200, 1200, false);
        $sizes['large'] = '/uploads/' . $category . '/large_' . $filename . '.jpg';

        // Generate WebP versions for modern browsers
        $webpPath = $categoryPath . '/' . $filename . '.webp';
        $this->resizeAndSaveWebp($image, $webpPath, 1200, 1200, false);
        $sizes['webp'] = '/uploads/' . $category . '/' . $filename . '.webp';

        // Original optimized
        $originalPath = $categoryPath . '/' . $filename . '.jpg';
        $this->resizeAndSave($image, $originalPath, $this->maxWidth, $this->maxHeight, false);
        $sizes['original'] = '/uploads/' . $category . '/' . $filename . '.jpg';

        imagedestroy($image);

        return $sizes;
    }

    /**
     * Get srcset string for responsive images
     */
    public function getSrcset(string $basePath): string
    {
        $base = pathinfo($basePath, PATHINFO_FILENAME);
        $dir = pathinfo($basePath, PATHINFO_DIRNAME);

        $sizes = [
            'small' => '500w',
            'medium' => '800w',
            'large' => '1200w',
        ];

        $srcset = [];
        foreach ($sizes as $size => $descriptor) {
            $srcset[] = $dir . '/' . $size . '_' . $base . '.jpg ' . $descriptor;
        }

        return implode(', ', $srcset);
    }

    /**
     * Generate HTML picture element with WebP support
     */
    public function getPictureElement(string $basePath, string $alt = '', string $classes = ''): string
    {
        $base = pathinfo($basePath, PATHINFO_FILENAME);
        $dir = pathinfo($basePath, PATHINFO_DIRNAME);

        $srcset = $this->getSrcset($basePath);

        return <<<HTML
<picture>
    <source srcset="$dir/$base.webp" type="image/webp">
    <source srcset="$srcset" type="image/jpeg">
    <img src="$dir/medium_$base.jpg" alt="$alt" class="$classes" loading="lazy">
</picture>
HTML;
    }

    /**
     * Generate lazy loading HTML with blur placeholder
     */
    public function getLazyLoadingHTML(string $basePath, string $alt = '', string $classes = ''): string
    {
        $base = pathinfo($basePath, PATHINFO_FILENAME);
        $dir = pathinfo($basePath, PATHINFO_DIRNAME);

        $srcset = $this->getSrcset($basePath);
        $thumbnailPath = $dir . '/thumb_' . $base . '.jpg';

        return <<<HTML
<img 
    src="$thumbnailPath" 
    data-src="$dir/medium_$base.jpg" 
    data-srcset="$srcset"
    alt="$alt" 
    class="lazy-load $classes" 
    loading="lazy">
HTML;
    }

    /**
     * Delete image and all its variants
     */
    public function deleteImage(string $filename, string $category = 'products'): bool
    {
        $categoryPath = $this->uploadDir . '/' . $category;
        $base = pathinfo($filename, PATHINFO_FILENAME);

        $variants = [
            'thumb_' . $base . '.jpg',
            'small_' . $base . '.jpg',
            'medium_' . $base . '.jpg',
            'large_' . $base . '.jpg',
            $base . '.webp',
            $base . '.jpg',
        ];

        foreach ($variants as $variant) {
            $path = $categoryPath . '/' . $variant;
            if (file_exists($path)) {
                unlink($path);
            }
        }

        return true;
    }

    /**
     * Get image dimensions
     */
    public function getImageDimensions(string $filePath): ?array
    {
        $size = @getimagesize($filePath);
        if (!$size) {
            return null;
        }

        return [
            'width' => $size[0],
            'height' => $size[1],
            'type' => $size[2],
        ];
    }

    /**
     * Check if file is valid image
     */
    private function isValidImage(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            return false;
        }

        $ext = strtolower($file->getClientOriginalExtension());
        return in_array($ext, $this->supportedFormats);
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
        $name = substr($name, 0, 50);

        return $name . '_' . uniqid() . '_' . time();
    }

    /**
     * Load image with GD library
     */
    private function loadImage(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) {
            return null;
        }

        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($path);
            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($path);
            default:
                return null;
        }
    }

    /**
     * Resize and save image as JPEG
     */
    private function resizeAndSave($image, string $path, int $maxWidth, int $maxHeight, bool $crop = false): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($crop) {
            // Square crop
            $size = min($width, $height);
            $x = ($width - $size) / 2;
            $y = ($height - $size) / 2;

            $cropped = imagecrop($image, [
                'x' => (int)$x,
                'y' => (int)$y,
                'width' => $size,
                'height' => $size,
            ]);

            $resized = imagescale($cropped, $maxWidth, $maxHeight, IMG_BILINEAR_FIXED);
            imagedestroy($cropped);
        } else {
            // Maintain aspect ratio
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            $resized = imagescale($image, $newWidth, $newHeight, IMG_BILINEAR_FIXED);
        }

        imagejpeg($resized, $path, $this->jpegQuality);
        imagedestroy($resized);
    }

    /**
     * Resize and save image as WebP
     */
    private function resizeAndSaveWebp($image, string $path, int $maxWidth, int $maxHeight, bool $crop = false): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($crop) {
            $size = min($width, $height);
            $x = ($width - $size) / 2;
            $y = ($height - $size) / 2;

            $cropped = imagecrop($image, [
                'x' => (int)$x,
                'y' => (int)$y,
                'width' => $size,
                'height' => $size,
            ]);

            $resized = imagescale($cropped, $maxWidth, $maxHeight, IMG_BILINEAR_FIXED);
            imagedestroy($cropped);
        } else {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            $resized = imagescale($image, $newWidth, $newHeight, IMG_BILINEAR_FIXED);
        }

        imagewebp($resized, $path, $this->webpQuality);
        imagedestroy($resized);
    }
}
