<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductStatusController extends Controller
{
    /**
     * Show or hide a product on the storefront.
     */
    public function update(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $product->update(['is_active' => $validated['is_active']]);

        $message = __($product->is_active ? 'admin.products.now_visible' : 'admin.products.now_hidden', ['name' => $product->name]);

        if ($request->expectsJson()) {
            return response()->json(['is_active' => $product->is_active, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
