<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * AssetOptimizationService - CDN and asset management
 * 
 * Provides asset optimization features:
 * - Image compression and resizing
 * - CDN integration
 * - Asset versioning
 * - Lazy loading support
 * - WebP conversion
 * - Static asset caching
 */
class AssetOptimizationService
{
    /**
     * Image quality settings
     */
    const IMAGE_QUALITY_HIGH = 90;
    const IMAGE_QUALITY_MEDIUM = 75;
    const IMAGE_QUALITY_LOW = 60;

    /**
     * Standard image sizes for responsive design
     */
    const STANDARD_SIZES = [
        'thumbnail' => ['width' => 150, 'height' => 150],
        'small' => ['width' => 300, 'height' => 300],
        'medium' => ['width' => 600, 'height' => 600],
        'large' => ['width' => 1200, 'height' => 1200],
        'avatar' => ['width' => 80, 'height' => 80],
        'profile' => ['width' => 200, 'height' => 200]
    ];

    /**
     * CDN configuration
     */
    protected array $cdnConfig;

    public function __construct()
    {
        $this->cdnConfig = config('cdn', []);
    }

    /**
     * Optimize and store image with multiple sizes
     * Note: This is a placeholder implementation. 
     * In production, integrate with image processing library like Intervention Image
     */
    public function optimizeImage(string $imagePath, string $storagePath, array $options = []): array
    {
        try {
            // For now, just copy the original file
            // In production, implement actual image optimization
            $content = file_get_contents($imagePath);
            Storage::put($storagePath, $content);

            // Simulate metadata
            $metadata = [
                'original_path' => $imagePath,
                'storage_path' => $storagePath,
                'file_size' => strlen($content),
                'optimized_at' => now()->toISOString()
            ];

            Log::info('Image optimization completed (placeholder)', [
                'original_path' => $imagePath,
                'storage_path' => $storagePath,
                'metadata' => $metadata
            ]);

            return [
                'success' => true,
                'images' => ['original' => $storagePath],
                'metadata' => $metadata
            ];

        } catch (\Exception $e) {
            Log::error('Image optimization failed', [
                'image_path' => $imagePath,
                'storage_path' => $storagePath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate responsive image HTML with lazy loading
     */
    public function generateResponsiveImageHtml(string $basePath, array $options = []): string
    {
        $options = array_merge([
            'alt' => '',
            'class' => '',
            'lazy' => true,
            'sizes' => '(max-width: 600px) 300px, (max-width: 1200px) 600px, 1200px',
            'webp_support' => true
        ], $options);

        $metadata = $this->getImageMetadata($basePath);
        if (!$metadata) {
            return $this->generateFallbackImage($options);
        }

        $picture = '<picture>';
        
        // WebP sources
        if ($options['webp_support'] && isset($metadata['webp_sources'])) {
            foreach ($metadata['webp_sources'] as $size => $path) {
                $url = $this->getCdnUrl($path);
                $mediaQuery = $this->getMediaQuery($size);
                $picture .= "<source media=\"{$mediaQuery}\" srcset=\"{$url}\" type=\"image/webp\">";
            }
        }

        // Standard image sources
        if (isset($metadata['sources'])) {
            foreach ($metadata['sources'] as $size => $path) {
                $url = $this->getCdnUrl($path);
                $mediaQuery = $this->getMediaQuery($size);
                $picture .= "<source media=\"{$mediaQuery}\" srcset=\"{$url}\">";
            }
        }

        // Fallback img tag
        $fallbackUrl = $this->getCdnUrl($metadata['fallback'] ?? $basePath);
        $imgAttributes = [
            'src' => $options['lazy'] ? '' : $fallbackUrl,
            'alt' => $options['alt'],
            'class' => $options['class'],
            'loading' => $options['lazy'] ? 'lazy' : 'eager',
            'sizes' => $options['sizes']
        ];

        if ($options['lazy']) {
            $imgAttributes['data-src'] = $fallbackUrl;
        }

        $img = '<img ' . $this->buildAttributes($imgAttributes) . '>';
        $picture .= $img . '</picture>';

        return $picture;
    }

    /**
     * Optimize CSS files
     */
    public function optimizeCSS(string $cssContent): string
    {
        // Remove comments
        $cssContent = preg_replace('/\/\*.*?\*\//s', '', $cssContent);
        
        // Remove unnecessary whitespace
        $cssContent = preg_replace('/\s+/', ' ', $cssContent);
        
        // Remove spaces around specific characters
        $cssContent = str_replace([' {', '{ ', ' }', '} ', ': ', ' :', '; ', ' ;'], ['{', '{', '}', '}', ':', ':', ';', ';'], $cssContent);
        
        return trim($cssContent);
    }

    /**
     * Optimize JavaScript files
     */
    public function optimizeJS(string $jsContent): string
    {
        // This is a basic optimization - in production, use a proper JS minifier
        
        // Remove single-line comments
        $jsContent = preg_replace('/\/\/.*$/m', '', $jsContent);
        
        // Remove multi-line comments
        $jsContent = preg_replace('/\/\*.*?\*\//s', '', $jsContent);
        
        // Remove unnecessary whitespace
        $jsContent = preg_replace('/\s+/', ' ', $jsContent);
        
        return trim($jsContent);
    }

    /**
     * Generate critical CSS for above-the-fold content
     */
    public function generateCriticalCSS(string $url, array $options = []): string
    {
        // This would integrate with a critical CSS service like Penthouse.js
        // For now, return a placeholder
        
        $cacheKey = 'critical_css_' . md5($url);
        
        return Cache::remember($cacheKey, 3600, function () use ($url, $options) {
            // In production, this would extract critical CSS
            return "/* Critical CSS for {$url} */";
        });
    }

    /**
     * Get CDN URL for asset
     */
    public function getCdnUrl(string $assetPath): string
    {
        if (empty($this->cdnConfig['enabled'])) {
            return Storage::url($assetPath);
        }

        $cdnDomain = $this->cdnConfig['domain'] ?? '';
        $version = $this->getAssetVersion($assetPath);
        
        return "{$cdnDomain}/{$assetPath}?v={$version}";
    }

    /**
     * Preload critical assets
     */
    public function generatePreloadTags(array $assets): string
    {
        $preloadTags = [];
        
        foreach ($assets as $asset) {
            $url = $this->getCdnUrl($asset['path']);
            $as = $asset['type'] ?? $this->getAssetType($asset['path']);
            $crossorigin = $asset['crossorigin'] ?? '';
            
            $attributes = [
                'rel' => 'preload',
                'href' => $url,
                'as' => $as
            ];
            
            if ($crossorigin) {
                $attributes['crossorigin'] = $crossorigin;
            }
            
            $preloadTags[] = '<link ' . $this->buildAttributes($attributes) . '>';
        }
        
        return implode("\n", $preloadTags);
    }

    /**
     * Generate resource hints
     */
    public function generateResourceHints(array $domains = []): string
    {
        $hints = [];
        
        // DNS prefetch for external domains
        foreach ($domains as $domain) {
            $hints[] = "<link rel=\"dns-prefetch\" href=\"//{$domain}\">";
        }
        
        // Preconnect to CDN
        if (!empty($this->cdnConfig['domain'])) {
            $cdnDomain = parse_url($this->cdnConfig['domain'], PHP_URL_HOST);
            $hints[] = "<link rel=\"preconnect\" href=\"//{$cdnDomain}\" crossorigin>";
        }
        
        return implode("\n", $hints);
    }

    /**
     * Process image with optimization settings
     * Note: Placeholder for image processing library integration
     */
    protected function processImage($image, array $options)
    {
        // Placeholder - implement with image processing library
        return $image;
    }

    /**
     * Resize image maintaining aspect ratio
     * Note: Placeholder for image processing library integration
     */
    protected function resizeImage($image, int $width, int $height)
    {
        // Placeholder - implement with image processing library
        return $image;
    }

    /**
     * Store processed image
     */
    protected function storeImage($image, string $basePath, string $size, string $format = null): string
    {
        // Placeholder implementation
        $pathInfo = pathinfo($basePath);
        $extension = $format ?: $pathInfo['extension'];
        $filename = $pathInfo['filename'] . '_' . $size . '.' . $extension;
        $fullPath = $pathInfo['dirname'] . '/' . $filename;

        // In production, this would store the actual processed image
        Storage::put($fullPath, ''); // Placeholder
        
        return $fullPath;
    }

    /**
     * Extract image metadata
     */
    protected function extractImageMetadata($originalImage, array $optimizedImages): array
    {
        return [
            'original_width' => 0, // Placeholder
            'original_height' => 0, // Placeholder
            'mime_type' => 'image/jpeg', // Placeholder
            'file_size' => 0, // Placeholder
            'generated_at' => now()->toISOString(),
            'sources' => array_filter($optimizedImages, function($key) {
                return !str_contains($key, '_webp');
            }, ARRAY_FILTER_USE_KEY),
            'webp_sources' => array_filter($optimizedImages, function($key) {
                return str_contains($key, '_webp');
            }, ARRAY_FILTER_USE_KEY),
            'fallback' => $optimizedImages['medium'] ?? $optimizedImages['original'] ?? null
        ];
    }

    /**
     * Store image metadata
     */
    protected function storeImageMetadata(string $basePath, array $metadata): void
    {
        $metadataPath = $basePath . '.metadata.json';
        Storage::put($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT));
    }

    /**
     * Get stored image metadata
     */
    protected function getImageMetadata(string $basePath): ?array
    {
        $metadataPath = $basePath . '.metadata.json';
        
        if (!Storage::exists($metadataPath)) {
            return null;
        }
        
        $content = Storage::get($metadataPath);
        return json_decode($content, true);
    }

    /**
     * Check WebP support
     */
    protected function supportsWebP(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromwebp');
    }

    /**
     * Get media query for responsive size
     */
    protected function getMediaQuery(string $size): string
    {
        $queries = [
            'thumbnail' => '(max-width: 300px)',
            'small' => '(max-width: 600px)',
            'medium' => '(max-width: 1200px)',
            'large' => '(min-width: 1201px)',
            'avatar' => '(max-width: 100px)',
            'profile' => '(max-width: 250px)'
        ];

        return $queries[$size] ?? '(min-width: 0px)';
    }

    /**
     * Generate fallback image HTML
     */
    protected function generateFallbackImage(array $options): string
    {
        return "<img src=\"data:image/svg+xml;base64," . base64_encode(
            '<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#f0f0f0"/><text x="50%" y="50%" text-anchor="middle" fill="#999">Image not available</text></svg>'
        ) . "\" alt=\"{$options['alt']}\" class=\"{$options['class']}\">";
    }

    /**
     * Build HTML attributes string
     */
    protected function buildAttributes(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $parts[] = $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        return implode(' ', $parts);
    }

    /**
     * Get asset version for cache busting
     */
    protected function getAssetVersion(string $assetPath): string
    {
        if (Storage::exists($assetPath)) {
            return substr(md5(Storage::lastModified($assetPath)), 0, 8);
        }
        
        return substr(md5($assetPath), 0, 8);
    }

    /**
     * Determine asset type from file extension
     */
    protected function getAssetType(string $assetPath): string
    {
        $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
        
        $types = [
            'css' => 'style',
            'js' => 'script',
            'woff' => 'font',
            'woff2' => 'font',
            'ttf' => 'font',
            'otf' => 'font',
            'jpg' => 'image',
            'jpeg' => 'image',
            'png' => 'image',
            'webp' => 'image',
            'svg' => 'image'
        ];
        
        return $types[$extension] ?? 'document';
    }
}
