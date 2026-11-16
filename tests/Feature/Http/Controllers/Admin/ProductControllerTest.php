<?php

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

describe('index', function () {
    it('lists active and inactive products', function () {
        Product::factory()->create(['name' => 'Visible Hoodie']);
        Product::factory()->inactive()->create(['name' => 'Hidden Sweater']);

        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

        $response->assertOk()->assertSee('Visible Hoodie')->assertSee('Hidden Sweater');
    });

    it('filters by search term and status', function () {
        Product::factory()->create(['name' => 'Visible Hoodie']);
        Product::factory()->inactive()->create(['name' => 'Hidden Hoodie']);

        $response = $this->actingAs($this->admin)->get(route('admin.products.index', ['q' => 'hoodie', 'status' => 'inactive']));

        $response->assertOk()->assertSee('Hidden Hoodie')->assertDontSee('Visible Hoodie');
    });
});

describe('store', function () {
    it('creates a product with an automatic slug and optimized images', function () {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name_en' => 'Premium Zipper Hoodie',
            'price' => '1,850',
            'description' => 'Warm and soft.',
            'is_active' => '1',
            'images' => [
                UploadedFile::fake()->image('front.jpg', 2400, 1800),
                UploadedFile::fake()->image('back.png', 800, 800),
            ],
        ]);

        $product = Product::firstWhere('slug', 'premium-zipper-hoodie');
        $response->assertRedirect(route('admin.products.index'))->assertSessionHas('success');
        expect($product)->not->toBeNull()
            ->and($product->price)->toBe(1850)
            ->and($product->is_active)->toBeTrue()
            ->and($product->images)->toHaveCount(2);

        $image = $product->images->first();
        Storage::disk('public')->assertExists([$image->path, $image->thumbnail_path]);
        expect($image->path)->toEndWith('.webp')
            ->and(getimagesizefromstring(Storage::disk('public')->get($image->path))[0])->toBe(1600)
            ->and(getimagesizefromstring(Storage::disk('public')->get($image->thumbnail_path))[0])->toBe(600);
    });

    it('gives duplicate names a unique slug', function () {
        Product::factory()->create(['name' => 'Hoodie']);

        $this->actingAs($this->admin)->post(route('admin.products.store'), ['name_en' => 'Hoodie', 'price' => 500, 'is_active' => '1']);

        $this->assertDatabaseHas('products', ['slug' => 'hoodie-2']);
    });

    it('requires a name in at least one language, a price and a status', function () {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), []);

        $response->assertSessionHasErrors([
            'name_bn' => 'Enter the product name in Bengali or English.',
            'name_en' => 'Enter the product name in Bengali or English.',
            'price',
            'is_active',
        ]);
        $this->assertDatabaseCount('products', 0);
    });

    it('creates a product with only a Bengali name', function () {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name_bn' => 'প্রিমিয়াম হুডি',
            'price' => 900,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['name_bn' => 'প্রিমিয়াম হুডি', 'name_en' => null]);
    });

    it('saves both translations of the name and description', function () {
        $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name_bn' => 'হাই নেক সোয়েটার',
            'name_en' => 'High Neck Sweater',
            'description_bn' => 'নরম উলের সোয়েটার।',
            'description_en' => 'A soft wool sweater.',
            'price' => 1200,
            'is_active' => '1',
        ]);

        $this->assertDatabaseHas('products', [
            'slug' => 'high-neck-sweater',
            'name_bn' => 'হাই নেক সোয়েটার',
            'name_en' => 'High Neck Sweater',
            'description_bn' => 'নরম উলের সোয়েটার।',
            'description_en' => 'A soft wool sweater.',
        ]);
    });

    it('rejects more than three images', function () {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name_en' => 'Hoodie',
            'price' => 500,
            'is_active' => '1',
            'images' => array_map(fn () => UploadedFile::fake()->image('photo.jpg'), range(1, 4)),
        ]);

        $response->assertSessionHasErrors(['images' => 'A product can have up to 3 images.']);
        $this->assertDatabaseCount('products', 0);
    });

    it('rejects files that are not allowed images', function (UploadedFile $file) {
        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'name_en' => 'Hoodie',
            'price' => 500,
            'is_active' => '1',
            'images' => [$file],
        ]);

        $response->assertSessionHasErrors('images.0');
        $this->assertDatabaseCount('products', 0);
    })->with([
        'php script' => fn () => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
        'gif image' => fn () => UploadedFile::fake()->image('animation.gif'),
        'over 5 MB' => fn () => UploadedFile::fake()->image('huge.jpg')->size(6000),
    ]);
});

describe('update', function () {
    it('updates details, removes selected images and adds new ones', function () {
        $product = Product::factory()->create(['name' => 'Old Name', 'price' => 100]);
        $keep = ProductImage::factory()->for($product)->create(['sort_order' => 1]);
        $remove = ProductImage::factory()->for($product)->create(['sort_order' => 2]);
        Storage::disk('public')->put($remove->path, 'image');

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name_en' => 'New Name',
            'price' => 950,
            'is_active' => '0',
            'slug' => '',
            'remove_images' => [$remove->id],
            'images' => [UploadedFile::fake()->image('new.jpg')],
        ]);

        $response->assertRedirect(route('admin.products.edit', $product))->assertSessionHas('success');
        $product->refresh();
        expect($product->name)->toBe('New Name')
            ->and($product->slug)->toBe('new-name')
            ->and($product->price)->toBe(950)
            ->and($product->is_active)->toBeFalse()
            ->and($product->images)->toHaveCount(2)
            ->and($product->images->first()->is($keep))->toBeTrue();
        $this->assertModelMissing($remove);
        Storage::disk('public')->assertMissing($remove->path);
    });

    it('keeps a custom slug when one is given', function () {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name_en' => 'Hoodie',
            'price' => 500,
            'is_active' => '1',
            'slug' => 'Winter Hoodie 2026',
        ]);

        expect($product->refresh()->slug)->toBe('winter-hoodie-2026');
    });

    it('rejects a slug used by another product', function () {
        Product::factory()->create(['slug' => 'taken']);
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name_en' => 'Hoodie',
            'price' => 500,
            'is_active' => '1',
            'slug' => 'taken',
        ]);

        $response->assertSessionHasErrors('slug');
    });

    it('rejects uploads that would exceed three images in total', function () {
        $product = Product::factory()->create();
        ProductImage::factory()->for($product)->count(2)->create();

        $response = $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name_en' => 'Hoodie',
            'price' => 500,
            'is_active' => '1',
            'images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ]);

        $response->assertSessionHasErrors(['images' => 'A product can have up to 3 images. Remove an existing image first.']);
        expect($product->images()->count())->toBe(2);
    });

    it('ignores image IDs that belong to another product', function () {
        $product = Product::factory()->create();
        $otherImage = ProductImage::factory()->create();

        $this->actingAs($this->admin)->put(route('admin.products.update', $product), [
            'name_en' => 'Hoodie',
            'price' => 500,
            'is_active' => '1',
            'remove_images' => [$otherImage->id],
        ]);

        $this->assertModelExists($otherImage);
    });
});

describe('destroy', function () {
    it('deletes the product and its image files', function () {
        $product = Product::factory()->create();
        $image = ProductImage::factory()->for($product)->create();
        Storage::disk('public')->put($image->path, 'image');
        Storage::disk('public')->put($image->thumbnail_path, 'image');

        $response = $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertModelMissing($product);
        $this->assertModelMissing($image);
        Storage::disk('public')->assertMissing([$image->path, $image->thumbnail_path]);
    });

    it('returns JSON for AJAX deletes', function () {
        $product = Product::factory()->create(['name' => 'Hoodie']);

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.products.destroy', $product));

        $response->assertOk()->assertJson(['message' => '“Hoodie” has been deleted.']);
        $this->assertModelMissing($product);
    });
});
