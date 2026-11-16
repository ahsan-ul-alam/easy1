<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Support\Localization;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     * A product needs a name in at least one language.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_bn' => ['nullable', 'string', 'max:255', 'required_without:name_en'],
            'name_en' => ['nullable', 'string', 'max:255', 'required_without:name_bn'],
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
            'description_bn' => ['nullable', 'string', 'max:10000'],
            'description_en' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['required', 'boolean'],
            'images' => ['nullable', 'array', 'max:'.Product::MAX_IMAGES],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=5000,max_height=5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name_bn.required_without' => __('admin.validation.name_required'),
            'name_en.required_without' => __('admin.validation.name_required'),
            'images.max' => __('admin.validation.images_max', ['max' => Localization::digits(Product::MAX_IMAGES)]),
            'images.*.image' => __('admin.validation.image_type'),
            'images.*.mimes' => __('admin.validation.image_type'),
            'images.*.max' => __('admin.validation.image_size'),
            'images.*.dimensions' => __('admin.validation.image_dimensions'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('price'))) {
            $this->merge(['price' => str_replace([',', ' ', '৳'], '', $this->input('price'))]);
        }
    }
}
