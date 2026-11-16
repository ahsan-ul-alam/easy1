<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

/**
 * Builds pre-filled WhatsApp order messages and wa.me links in the visitor's language.
 * Messages are only opened in WhatsApp; the customer always presses Send.
 */
class WhatsAppOrder
{
    /**
     * Keep links comfortably below the length browsers and WhatsApp handle reliably.
     */
    public const MAX_URL_LENGTH = 4000;

    private const DIVIDER = '--------------------';

    public function __construct(private SiteSettings $settings) {}

    /**
     * Whether a WhatsApp number has been configured.
     */
    public function isAvailable(): bool
    {
        return $this->settings->whatsappNumber() !== null;
    }

    public function singleProductMessage(Product $product, int $quantity): string
    {
        $quantity = max(1, min(Cart::MAX_QUANTITY, $quantity));

        return implode("\n", [
            __('whatsapp.greeting'),
            '',
            __('whatsapp.single.intro'),
            '',
            __('whatsapp.single.product', ['name' => $product->name]),
            __('whatsapp.single.quantity', ['quantity' => self::quantity($quantity)]),
            __('whatsapp.single.unit_price', ['price' => self::money($product->price)]),
            __('whatsapp.single.total', ['total' => self::money($product->price * $quantity)]),
            '',
            __('whatsapp.single.link'),
            route('products.show', $product),
            '',
            __('whatsapp.single.closing'),
        ]);
    }

    /**
     * The order message for a whole cart, condensed when needed so the link stays usable.
     */
    public function cartMessage(Cart $cart): string
    {
        $message = $this->buildCartMessage($cart, compact: false);

        if ($this->fits($message)) {
            return $message;
        }

        for ($shown = $cart->lines->count(); $shown >= 1; $shown--) {
            $message = $this->buildCartMessage($cart, compact: true, shownLines: $shown);

            if ($this->fits($message)) {
                return $message;
            }
        }

        return $message;
    }

    /**
     * The wa.me link for the message, or null when no WhatsApp number is configured.
     */
    public function url(string $message): ?string
    {
        $number = $this->settings->whatsappNumber();

        if ($number === null) {
            return null;
        }

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    /**
     * Convert a phone number to the digits-only international format wa.me expects.
     * Local Bangladeshi numbers such as 01712-345678 become 8801712345678.
     */
    public static function normalizeNumber(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '01')) {
            $digits = '88'.$digits;
        }

        return strlen($digits) >= 8 && strlen($digits) <= 15 ? $digits : null;
    }

    /**
     * A price in the message's language, e.g. "BDT 1,700" or "৳১,৭০০".
     */
    public static function money(int $amount): string
    {
        return __('whatsapp.money', ['amount' => Localization::number($amount)]);
    }

    /**
     * A quantity in the message's language, e.g. "2" or "২টি".
     */
    public static function quantity(int $count): string
    {
        return __('whatsapp.quantity', ['count' => Localization::number($count)]);
    }

    private function buildCartMessage(Cart $cart, bool $compact, ?int $shownLines = null): string
    {
        $lines = [__('whatsapp.greeting'), '', __('whatsapp.cart.intro'), '', __('whatsapp.cart.details'), ''];
        $shown = $cart->lines->take($shownLines ?? $cart->lines->count());

        foreach ($shown->values() as $index => $line) {
            $number = Localization::digits($index + 1);
            $name = $line['product']->name;

            if ($compact) {
                $lines[] = __('whatsapp.cart.compact_line', [
                    'number' => $number,
                    'name' => Str::limit($name, 40),
                    'quantity' => self::quantity($line['quantity']),
                    'subtotal' => self::money($line['subtotal']),
                ]);

                continue;
            }

            array_push(
                $lines,
                "{$number}. {$name}",
                __('whatsapp.cart.quantity', ['quantity' => self::quantity($line['quantity'])]),
                __('whatsapp.cart.unit_price', ['price' => self::money($line['product']->price)]),
                __('whatsapp.cart.subtotal', ['subtotal' => self::money($line['subtotal'])]),
                '',
            );
        }

        $hidden = $cart->lines->count() - $shown->count();

        if ($hidden > 0) {
            $lines[] = trans_choice('whatsapp.cart.more', $hidden, ['count' => Localization::number($hidden)]);
        }

        if ($compact) {
            $lines[] = '';
        }

        array_push(
            $lines,
            self::DIVIDER,
            __('whatsapp.cart.total_items', ['count' => self::quantity($cart->totalItems())]),
            __('whatsapp.cart.grand_total', ['total' => self::money($cart->grandTotal())]),
            self::DIVIDER,
            '',
            __('whatsapp.cart.closing'),
        );

        return implode("\n", $lines);
    }

    private function fits(string $message): bool
    {
        return strlen('https://wa.me/'.$this->settings->whatsappNumber().'?text='.rawurlencode($message)) <= self::MAX_URL_LENGTH;
    }
}
