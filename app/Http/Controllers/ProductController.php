<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use App\Support\WhatsAppOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('images');

        $relatedProducts = Product::query()
            ->active()
            ->whereKeyNot($product->id)
            ->with('images')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('storefront.product', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * Open WhatsApp with a pre-filled order message for a single product.
     */
    public function order(Request $request, Product $product, WhatsAppOrder $whatsApp): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:'.Cart::MAX_QUANTITY],
        ]);

        $url = $whatsApp->url($whatsApp->singleProductMessage($product, (int) ($validated['quantity'] ?? 1)));

        if ($url === null) {
            return redirect()->route('products.show', $product)
                ->with('error', __('cart.errors.whatsapp_unavailable'));
        }

        return redirect()->away($url);
    }
}
