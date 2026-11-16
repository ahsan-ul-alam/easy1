<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Support\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Product fields saved from the admin form (images and slug are handled separately).
     */
    private const FORM_FIELDS = ['name_bn', 'name_en', 'price', 'description_bn', 'description_en', 'is_active'];

    /**
     * List products with search and status filters. AJAX requests receive only the results fragment.
     */
    public function index(Request $request): View|string
    {
        $search = Str::limit(trim(self::queryString($request, 'q')), 100, '');
        $status = self::queryString($request, 'status');

        $products = Product::query()
            ->search($search)
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->with('images')
            ->latest()
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search,
            'status' => $status,
        ])->fragmentIf($request->ajax(), 'results');
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product(['is_active' => true])]);
    }

    public function store(StoreProductRequest $request, ImageOptimizer $optimizer): RedirectResponse
    {
        $product = DB::transaction(function () use ($request, $optimizer): Product {
            $product = Product::create($request->safe()->only(self::FORM_FIELDS));

            $this->storeImages($product, $request->file('images', []), $optimizer);

            return $product;
        });

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.products.created', ['name' => $product->name]));
    }

    public function edit(Product $product): View
    {
        $product->load('images');

        return view('admin.products.form', ['product' => $product]);
    }

    public function update(UpdateProductRequest $request, Product $product, ImageOptimizer $optimizer): RedirectResponse
    {
        DB::transaction(function () use ($request, $product, $optimizer): void {
            $slug = $request->validated('slug');
            $product->fill($request->safe()->only(self::FORM_FIELDS));
            $product->slug = filled($slug) ? Str::slug($slug) : Product::uniqueSlugFor($product->slugSource(), $product->id);
            $product->save();

            $product->images()
                ->whereKey($request->validated('remove_images', []))
                ->get()
                ->each
                ->delete();

            $this->storeImages($product, $request->file('images', []), $optimizer);
        });

        return redirect()->route('admin.products.edit', $product)
            ->with('success', __('admin.flash.saved'));
    }

    public function destroy(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $product->delete();

        $message = __('admin.products.deleted', ['name' => $product->name]);

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('admin.products.index')->with('success', $message);
    }

    /**
     * Optimize and attach uploaded images after the product's existing ones.
     *
     * @param  array<int, UploadedFile>  $files
     */
    private function storeImages(Product $product, array $files, ImageOptimizer $optimizer): void
    {
        $nextPosition = (int) $product->images()->max('sort_order') + 1;

        foreach ($files as $file) {
            $product->images()->create([
                ...$optimizer->storeProductImage($file),
                'sort_order' => $nextPosition++,
            ]);
        }
    }
}
