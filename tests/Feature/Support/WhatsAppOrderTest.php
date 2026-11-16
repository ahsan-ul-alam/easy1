<?php

use App\Models\Product;
use App\Support\Cart;
use App\Support\SiteSettings;
use App\Support\WhatsAppOrder;

beforeEach(function () {
    app(SiteSettings::class)->update(['whatsapp_number' => '8801712345678']);
});

describe('in English', function () {
    it('builds the single product message with quantity, totals and product link', function () {
        $product = Product::factory()->create(['name_en' => 'Premium Zipper Hoodie', 'price' => 850]);

        $message = app(WhatsAppOrder::class)->singleProductMessage($product, 2);

        expect($message)->toBe(implode("\n", [
            'Hello,',
            '',
            'I would like to order the following product.',
            '',
            'Product: Premium Zipper Hoodie',
            'Quantity: 2',
            'Unit Price: BDT 850',
            'Total: BDT 1,700',
            '',
            'Product Link:',
            url('/products/premium-zipper-hoodie'),
            '',
            'Please confirm my order.',
        ]));
    });

    it('builds the cart message with subtotals, total items and grand total', function () {
        $hoodie = Product::factory()->create(['name_en' => 'Premium Black Hoodie', 'price' => 850]);
        $sweater = Product::factory()->create(['name_en' => 'High Neck Sweater', 'price' => 1200]);
        $cart = Cart::fromItems([
            ['id' => $hoodie->id, 'quantity' => 2],
            ['id' => $sweater->id, 'quantity' => 1],
        ]);

        $message = app(WhatsAppOrder::class)->cartMessage($cart);

        expect($message)->toBe(implode("\n", [
            'Hello,',
            '',
            'I would like to place an order.',
            '',
            'Order Details:',
            '',
            '1. Premium Black Hoodie',
            'Quantity: 2',
            'Unit Price: BDT 850',
            'Subtotal: BDT 1,700',
            '',
            '2. High Neck Sweater',
            'Quantity: 1',
            'Unit Price: BDT 1,200',
            'Subtotal: BDT 1,200',
            '',
            '--------------------',
            'Total Items: 3',
            'Grand Total: BDT 2,900',
            '--------------------',
            '',
            'Please confirm my order.',
        ]));
    });
});

describe('in Bengali', function () {
    beforeEach(fn () => app()->setLocale('bn'));

    it('builds the single product message with Bengali text and digits', function () {
        $product = Product::factory()->create(['name_bn' => 'প্রিমিয়াম জিপার হুডি', 'name_en' => 'Premium Zipper Hoodie', 'price' => 850]);

        $message = app(WhatsAppOrder::class)->singleProductMessage($product, 2);

        expect($message)->toBe(implode("\n", [
            'আসসালামু আলাইকুম,',
            '',
            'আমি নিচের পণ্যটি অর্ডার করতে চাই।',
            '',
            'পণ্যের নাম: প্রিমিয়াম জিপার হুডি',
            'পরিমাণ: ২টি',
            'প্রতি পিসের দাম: ৳৮৫০',
            'মোট মূল্য: ৳১,৭০০',
            '',
            'পণ্যের লিংক:',
            url('/products/premium-zipper-hoodie'),
            '',
            'অনুগ্রহ করে অর্ডারটি নিশ্চিত করুন।',
        ]));
    });

    it('builds the cart message with Bengali text and digits', function () {
        $hoodie = Product::factory()->create(['name_bn' => 'প্রিমিয়াম ব্ল্যাক হুডি', 'price' => 850]);
        $sweater = Product::factory()->create(['name_bn' => 'হাই নেক সোয়েটার', 'price' => 1200]);
        $cart = Cart::fromItems([
            ['id' => $hoodie->id, 'quantity' => 2],
            ['id' => $sweater->id, 'quantity' => 1],
        ]);

        $message = app(WhatsAppOrder::class)->cartMessage($cart);

        expect($message)->toBe(implode("\n", [
            'আসসালামু আলাইকুম,',
            '',
            'আমি নিচের পণ্যগুলো অর্ডার করতে চাই।',
            '',
            'অর্ডারের বিবরণ:',
            '',
            '১. প্রিমিয়াম ব্ল্যাক হুডি',
            'পরিমাণ: ২টি',
            'একক মূল্য: ৳৮৫০',
            'মোট: ৳১,৭০০',
            '',
            '২. হাই নেক সোয়েটার',
            'পরিমাণ: ১টি',
            'একক মূল্য: ৳১,২০০',
            'মোট: ৳১,২০০',
            '',
            '--------------------',
            'মোট পণ্য: ৩টি',
            'সর্বমোট মূল্য: ৳২,৯০০',
            '--------------------',
            '',
            'অনুগ্রহ করে আমার অর্ডারটি নিশ্চিত করুন।',
        ]));
    });

    it('uses the English product name when no Bengali name exists', function () {
        $product = Product::factory()->create(['name_bn' => null, 'name_en' => 'Canvas Tote', 'price' => 500]);

        $message = app(WhatsAppOrder::class)->singleProductMessage($product, 1);

        expect($message)->toContain('পণ্যের নাম: Canvas Tote');
    });

    it('condenses very long Bengali cart messages within the link limit', function () {
        $products = Product::factory()
            ->count(Cart::MAX_LINES)
            ->sequence(fn ($sequence) => ['name_bn' => str_repeat('অনেক লম্বা পণ্যের নাম ', 8).$sequence->index])
            ->create(['price' => 1000]);
        $cart = Cart::fromItems($products->map(fn (Product $product) => ['id' => $product->id, 'quantity' => 2])->all());
        $whatsApp = app(WhatsAppOrder::class);

        $url = $whatsApp->url($whatsApp->cartMessage($cart));

        expect(strlen($url))->toBeLessThanOrEqual(WhatsAppOrder::MAX_URL_LENGTH)
            ->and(rawurldecode($url))->toContain('মোট পণ্য: ১০০টি')
            ->and(rawurldecode($url))->toContain('সর্বমোট মূল্য: ৳১০০,০০০');
    });
});

it('condenses very long cart messages while keeping the correct grand total', function () {
    $products = Product::factory()
        ->count(Cart::MAX_LINES)
        ->sequence(fn ($sequence) => ['name' => str_repeat('Extra Long Product Name ', 10).$sequence->index])
        ->create(['price' => 1000]);
    $cart = Cart::fromItems($products->map(fn (Product $product) => ['id' => $product->id, 'quantity' => 2])->all());
    $whatsApp = app(WhatsAppOrder::class);

    $url = $whatsApp->url($whatsApp->cartMessage($cart));

    expect(strlen($url))->toBeLessThanOrEqual(WhatsAppOrder::MAX_URL_LENGTH)
        ->and(rawurldecode($url))->toContain('Total Items: 100')
        ->and(rawurldecode($url))->toContain('Grand Total: BDT 100,000')
        ->and(rawurldecode($url))->toContain('more products');
});

it('creates a wa.me link with the URL-encoded message', function () {
    $url = app(WhatsAppOrder::class)->url("Hello & welcome\nLine 2");

    expect($url)->toBe('https://wa.me/8801712345678?text=Hello%20%26%20welcome%0ALine%202');
});

it('URL-encodes Bengali text as UTF-8', function () {
    $url = app(WhatsAppOrder::class)->url('৳৮৫০');

    expect($url)->toBe('https://wa.me/8801712345678?text=%E0%A7%B3%E0%A7%AE%E0%A7%AB%E0%A7%A6')
        ->and(rawurldecode($url))->toEndWith('৳৮৫০');
});

it('returns no link when no WhatsApp number is configured', function () {
    app(SiteSettings::class)->update(['whatsapp_number' => null]);

    expect(app(WhatsAppOrder::class)->url('Hello'))->toBeNull();
});

it('normalizes phone numbers to the wa.me format', function (?string $input, ?string $expected) {
    expect(WhatsAppOrder::normalizeNumber($input))->toBe($expected);
})->with([
    'local Bangladeshi number' => ['01712-345678', '8801712345678'],
    'international with plus' => ['+880 1712 345678', '8801712345678'],
    'international with 00 prefix' => ['008801712345678', '8801712345678'],
    'other country' => ['+44 7700 900123', '447700900123'],
    'too short' => ['12345', null],
    'empty' => [null, null],
]);
