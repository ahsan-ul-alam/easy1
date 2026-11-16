<?php

namespace App\Models;

use App\Support\Localization;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property-read string $name The name in the current language, falling back to the other language.
 * @property-read string|null $description The description in the current language, falling back to the other language.
 */
#[Fillable(['name_bn', 'name_en', 'slug', 'price', 'description_bn', 'description_en', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * The maximum number of images a product may have.
     */
    public const MAX_IMAGES = 3;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = static::uniqueSlugFor($product->slugSource());
            }
        });

        static::deleting(function (Product $product): void {
            $product->images->each->delete();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Limit the query to products visible on the storefront.
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Search products by name or description in either language.
     */
    #[Scope]
    protected function search(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

        return $query->where(function (Builder $query) use ($like): void {
            foreach (['name_bn', 'name_en', 'description_bn', 'description_en'] as $column) {
                $query->orWhereRaw("{$column} LIKE ? ESCAPE '!'", [$like]);
            }
        });
    }

    /**
     * The name in the current language. Setting it writes the current language's name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (): string => (string) $this->translated('name'),
            set: fn (?string $value): array => ['name_'.Localization::current() => $value],
        );
    }

    /**
     * The description in the current language. Setting it writes the current language's description.
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->translated('description'),
            set: fn (?string $value): array => ['description_'.Localization::current() => $value],
        );
    }

    /**
     * The price formatted for display, e.g. "৳1,700" or "৳১,৭০০".
     */
    protected function priceLabel(): Attribute
    {
        return Attribute::get(fn (): string => Localization::money($this->price));
    }

    /**
     * A plain-text excerpt of the description for cards and meta tags.
     */
    protected function excerpt(): Attribute
    {
        return Attribute::get(fn (): string => Str::limit(Str::squish(strip_tags((string) $this->description)), 155));
    }

    /**
     * The value of a translated field in the current language, or in the other language when missing.
     */
    public function translated(string $field, ?string $locale = null): ?string
    {
        $locale ??= Localization::current();

        return $this->attributes["{$field}_{$locale}"] ?? null
            ?: $this->attributes["{$field}_".Localization::other($locale)] ?? null;
    }

    /**
     * The text the URL slug is generated from, preferring the English name for readable URLs.
     */
    public function slugSource(): string
    {
        return (string) ($this->attributes['name_en'] ?? null ?: $this->attributes['name_bn'] ?? '');
    }

    /**
     * Generate a slug from the given name that no other product uses.
     */
    public static function uniqueSlugFor(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $base = Str::limit($base, 180, '');
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
