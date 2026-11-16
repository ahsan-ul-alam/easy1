<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Storage;

/**
 * Website settings editable from the admin panel, cached for fast reads.
 * Language-specific settings are stored per language (e.g. hero_title_bn, hero_title_en)
 * and read in the current language, falling back to the other language, then to a built-in default.
 *
 * @property-read string $business_name
 * @property-read string|null $logo_path
 * @property-read string|null $whatsapp_number
 * @property-read string|null $phone
 * @property-read string|null $email
 * @property-read string|null $facebook_url
 * @property-read string|null $instagram_url
 * @property-read string|null $tagline
 * @property-read string|null $address
 * @property-read string|null $business_hours
 * @property-read string $hero_title
 * @property-read string $hero_subtitle
 * @property-read string|null $contact_intro
 * @property-read string|null $meta_description
 * @property-read string|null $privacy_policy
 */
class SiteSettings
{
    public const CACHE_KEY = 'site_settings';

    /**
     * Settings shared by both languages.
     */
    public const SHARED = ['business_name', 'logo_path', 'whatsapp_number', 'phone', 'email', 'facebook_url', 'instagram_url'];

    /**
     * Settings with a Bengali and an English version.
     */
    public const TRANSLATABLE = ['tagline', 'address', 'business_hours', 'hero_title', 'hero_subtitle', 'contact_intro', 'meta_description', 'privacy_policy'];

    /**
     * Built-in defaults for translatable settings, as translation keys.
     */
    private const TRANSLATED_DEFAULTS = [
        'hero_title' => 'app.home.default_hero_title',
        'hero_subtitle' => 'app.home.default_hero_subtitle',
        'contact_intro' => 'app.contact.default_intro',
    ];

    /**
     * @var array<string, mixed>|null
     */
    private ?array $values = null;

    public function __construct(private CacheRepository $cache) {}

    /**
     * Every storable setting key.
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        $translatedKeys = [];

        foreach (self::TRANSLATABLE as $key) {
            foreach (array_keys(Localization::LOCALES) as $locale) {
                $translatedKeys[] = "{$key}_{$locale}";
            }
        }

        return [...self::SHARED, ...$translatedKeys];
    }

    /**
     * All stored settings, with the business name defaulting to the app name.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $stored = $this->cache->rememberForever(
            self::CACHE_KEY,
            fn (): array => Setting::query()->pluck('value', 'key')->all(),
        );

        $stored = array_filter($stored, fn (mixed $value): bool => $value !== null && $value !== '');

        return $this->values = array_merge(['business_name' => config('app.name')], $stored);
    }

    /**
     * Read a setting. Translatable settings return the current language's value with fallbacks.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (in_array($key, self::TRANSLATABLE, true)) {
            return $this->translated($key) ?? $default;
        }

        return $this->all()[$key] ?? $default;
    }

    /**
     * A translatable setting in the given language, falling back to the other language and then the default.
     */
    public function translated(string $key, ?string $locale = null): ?string
    {
        $locale ??= Localization::current();
        $values = $this->all();

        $value = $values["{$key}_{$locale}"] ?? $values["{$key}_".Localization::other($locale)] ?? null;

        if ($value === null && isset(self::TRANSLATED_DEFAULTS[$key])) {
            return __(self::TRANSLATED_DEFAULTS[$key], [], $locale);
        }

        return $value;
    }

    /**
     * The stored value for a key exactly as saved (no fallbacks), for admin forms.
     */
    public function raw(string $key): ?string
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * Persist the given settings and refresh the cache.
     *
     * @param  array<string, string|null>  $values
     */
    public function update(array $values): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, self::keys(), true)) {
                Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        $this->cache->forget(self::CACHE_KEY);
        $this->values = null;
    }

    /**
     * The WhatsApp number in international format (digits only), or null when not configured.
     */
    public function whatsappNumber(): ?string
    {
        return WhatsAppOrder::normalizeNumber($this->get('whatsapp_number'));
    }

    public function logoUrl(): ?string
    {
        $path = $this->get('logo_path');

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function __isset(string $key): bool
    {
        return $this->get($key) !== null;
    }
}
