<?php

namespace App\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Uri;

/**
 * Supported languages and locale-aware formatting helpers.
 * Bengali is the default for every visitor; English is available through the language switcher.
 */
final class Localization
{
    public const DEFAULT = 'bn';

    public const COOKIE = 'locale';

    public const QUERY = 'lang';

    /**
     * @var array<string, array{name: string, short: string}>
     */
    public const LOCALES = [
        'bn' => ['name' => 'বাংলা', 'short' => 'BN'],
        'en' => ['name' => 'English', 'short' => 'EN'],
    ];

    private const BENGALI_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    public static function isSupported(mixed $locale): bool
    {
        return is_string($locale) && array_key_exists($locale, self::LOCALES);
    }

    public static function current(): string
    {
        $locale = App::getLocale();

        return self::isSupported($locale) ? $locale : self::DEFAULT;
    }

    /**
     * The other supported language, used as the fallback for missing translated content.
     */
    public static function other(?string $locale = null): string
    {
        return ($locale ?? self::current()) === 'bn' ? 'en' : 'bn';
    }

    /**
     * Convert Western digits to Bengali digits when the locale is Bengali.
     */
    public static function digits(string|int $value, ?string $locale = null): string
    {
        $value = (string) $value;

        if (($locale ?? self::current()) !== 'bn') {
            return $value;
        }

        return str_replace(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], self::BENGALI_DIGITS, $value);
    }

    /**
     * A whole number with thousands separators, e.g. "1,700" or "১,৭০০".
     */
    public static function number(int $value, ?string $locale = null): string
    {
        return self::digits(number_format($value), $locale);
    }

    /**
     * A price for display on the website, e.g. "৳1,700" or "৳১,৭০০".
     */
    public static function money(int $amount, ?string $locale = null): string
    {
        return '৳'.self::number($amount, $locale);
    }

    /**
     * The URL of the given page in a specific language. The default language uses the plain URL.
     */
    public static function url(string $url, string $locale): string
    {
        $uri = Uri::of($url)->withoutQuery([self::QUERY]);

        if ($locale !== self::DEFAULT) {
            $uri = $uri->withQuery([self::QUERY => $locale]);
        }

        return (string) $uri;
    }

    /**
     * Link for the language switcher: the current page in the chosen language.
     */
    public static function switchUrl(string $locale): string
    {
        return (string) Uri::of(request()->fullUrl())->withQuery([self::QUERY => $locale]);
    }
}
