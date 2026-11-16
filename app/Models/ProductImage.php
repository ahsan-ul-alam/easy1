<?php

namespace App\Models;

use Database\Factories\ProductImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['path', 'thumbnail_path', 'sort_order'])]
class ProductImage extends Model
{
    /** @use HasFactory<ProductImageFactory> */
    use HasFactory;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::deleted(function (ProductImage $image): void {
            $localFiles = array_filter([$image->path, $image->thumbnail_path], fn (?string $path): bool => $path !== null && ! self::isExternal($path));

            Storage::disk('public')->delete($localFiles);
        });
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Public URL of the full-size image.
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => self::publicUrl($this->path));
    }

    /**
     * Public URL of the thumbnail image.
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn (): string => self::publicUrl($this->thumbnail_path));
    }

    /**
     * Uploaded images are stored on the public disk; demo images may be full https:// links.
     */
    private static function publicUrl(string $path): string
    {
        return self::isExternal($path) ? $path : Storage::disk('public')->url($path);
    }

    private static function isExternal(string $path): bool
    {
        return str_starts_with($path, 'https://') || str_starts_with($path, 'http://');
    }
}
