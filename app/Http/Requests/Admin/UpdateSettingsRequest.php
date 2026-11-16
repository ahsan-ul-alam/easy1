<?php

namespace App\Http\Requests\Admin;

use App\Support\Localization;
use App\Support\WhatsAppOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Maximum length of each language-specific setting.
     *
     * @var array<string, int>
     */
    public const TRANSLATABLE_MAX_LENGTHS = [
        'tagline' => 150,
        'address' => 500,
        'business_hours' => 150,
        'hero_title' => 120,
        'hero_subtitle' => 300,
        'contact_intro' => 1000,
        'meta_description' => 160,
        'privacy_policy' => 10000,
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'business_name' => ['required', 'string', 'max:100'],
            'whatsapp_number' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]+$/'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'facebook_url' => ['nullable', 'url:http,https', 'max:255'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];

        foreach (self::TRANSLATABLE_MAX_LENGTHS as $key => $maxLength) {
            foreach (array_keys(Localization::LOCALES) as $locale) {
                $rules["{$key}_{$locale}"] = ['nullable', 'string', 'max:'.$maxLength];
            }
        }

        return $rules;
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
                $number = $this->input('whatsapp_number');

                if (filled($number) && ! $validator->errors()->has('whatsapp_number') && WhatsAppOrder::normalizeNumber($number) === null) {
                    $validator->errors()->add('whatsapp_number', __('admin.validation.whatsapp_invalid'));
                }
            },
        ];
    }
}
