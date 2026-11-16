<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Support\Cart;
use App\Support\WhatsAppOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * The cart page. Items live in the browser and are loaded through sync().
     */
    public function index(): View
    {
        return view('storefront.cart');
    }

    /**
     * Return current names, prices and totals for the items in the browser cart.
     */
    public function sync(CartRequest $request, WhatsAppOrder $whatsApp): JsonResponse
    {
        $cart = Cart::fromItems($request->items());

        return response()->json([
            ...$cart->toArray(),
            'whatsapp_available' => $whatsApp->isAvailable(),
        ]);
    }

    /**
     * Revalidate the cart against the database and open WhatsApp with the order message.
     */
    public function order(CartRequest $request, WhatsAppOrder $whatsApp): RedirectResponse
    {
        $cart = Cart::fromItems($request->items());

        if ($cart->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', __('cart.errors.empty'));
        }

        $url = $whatsApp->url($whatsApp->cartMessage($cart));

        if ($url === null) {
            return redirect()->route('cart.index')
                ->with('error', __('cart.errors.whatsapp_unavailable'));
        }

        return redirect()->away($url);
    }
}
