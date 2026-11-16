<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Resizes and re-encodes uploaded images to WebP with GD.
 * Re-encoding also strips metadata and any non-image payload.
 * Falls back to storing the original file when GD cannot process it.
 */
class ImageOptimizer
{
    private const PRODUCT_MAX_DIMENSION = 1600;

    private const THUMBNAIL_MAX_DIMENSION = 600;

    private const LOGO_MAX_DIMENSION = 600;

    private const QUALITY = 82;

    /**
     * Store a product image and its thumbnail on the public disk.
     *
     * @return array{path: string, thumbnail_path: string}
     */
    public function storeProductImage(UploadedFile $file): array
    {
        $image = $this->load($file);

        if ($image === null) {
            $path = $file->store('products', 'public');

            return ['path' => $path, 'thumbnail_path' => $path];
        }

        $name = Str::random(40).'.webp';
        $path = "products/{$name}";
        $thumbnailPath = "products/thumbs/{$name}";

        Storage::disk('public')->put($path, $this->encode($this->resize($image, self::PRODUCT_MAX_DIMENSION)));
        Storage::disk('public')->put($thumbnailPath, $this->encode($this->resize($image, self::THUMBNAIL_MAX_DIMENSION)));

        return ['path' => $path, 'thumbnail_path' => $thumbnailPath];
    }

    /**
     * Store the website logo on the public disk and return its path.
     */
    public function storeLogo(UploadedFile $file): string
    {
        $image = $this->load($file);

        if ($image === null) {
            return $file->store('branding', 'public');
        }

        $path = 'branding/'.Str::random(40).'.webp';

        Storage::disk('public')->put($path, $this->encode($this->resize($image, self::LOGO_MAX_DIMENSION)));

        return $path;
    }

    private function load(UploadedFile $file): ?GdImage
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return null;
        }

        $this->raiseMemoryLimit();

        $contents = file_get_contents($file->getRealPath());
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if (! $image instanceof GdImage) {
            return null;
        }

        imagepalettetotruecolor($image);

        return $this->applyExifOrientation($image, $file);
    }

    /**
     * Rotate photos taken on phones so they display upright once metadata is stripped.
     */
    private function applyExifOrientation(GdImage $image, UploadedFile $file): GdImage
    {
        if (! function_exists('exif_read_data') || $file->getMimeType() !== 'image/jpeg') {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated instanceof GdImage ? $rotated : $image;
    }

    private function resize(GdImage $image, int $maxDimension): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, $maxDimension / max($width, $height));

        if ($scale >= 1) {
            return $image;
        }

        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $canvas;
    }

    private function encode(GdImage $image): string
    {
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, self::QUALITY);

        return (string) ob_get_clean();
    }

    /**
     * Large phone photos need more memory to decode than many shared hosts allow by default.
     */
    private function raiseMemoryLimit(): void
    {
        $limit = ini_get('memory_limit');

        if ($limit !== false && $limit !== '-1' && $this->bytes($limit) < 256 * 1024 * 1024) {
            @ini_set('memory_limit', '256M');
        }
    }

    private function bytes(string $value): int
    {
        $number = (int) $value;

        return match (strtolower(substr(trim($value), -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
