<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Support\Localization;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends StoreProductRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash', Rule::unique('products', 'slug')->ignore($this->route('product'))],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['images', 'remove_images'])) {
                    return;
                }

                /** @var Product $product */
                $product = $this->route('product');
                $removed = $product->images()->whereKey($this->input('remove_images', []))->count();
                $total = $product->images()->count() - $removed + count($this->file('images', []));

                if ($total > Product::MAX_IMAGES) {
                    $validator->errors()->add('images', __('admin.validation.images_total', ['max' => Localization::digits(Product::MAX_IMAGES)]));
                }
            },
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if (filled($this->input('slug'))) {
            $this->merge(['slug' => Str::slug($this->input('slug'))]);
        }
    }
}
