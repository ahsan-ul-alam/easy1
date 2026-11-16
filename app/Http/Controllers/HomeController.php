<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the storefront with a searchable product grid.
     * AJAX searches receive only the results fragment.
     */
    public function __invoke(Request $request): View|string
    {
        $search = Str::limit(trim(self::queryString($request, 'q')), 100, '');

        $products = Product::query()
            ->active()
            ->search($search)
            ->with('images')
            ->latest()
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('storefront.home', [
            'products' => $products,
            'search' => $search,
        ])->fragmentIf($request->ajax(), 'results');
    }
}
