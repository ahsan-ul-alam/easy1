<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Split language-specific website settings into Bengali (_bn) and English (_en) versions.
 * Saved values are kept: Bengali-script text moves to the _bn key, anything else to _en.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $translatableKeys = ['tagline', 'address', 'business_hours', 'hero_title', 'hero_subtitle', 'meta_description', 'privacy_policy'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->translatableKeys as $key) {
            $setting = DB::table('settings')->where('key', $key)->first();

            if ($setting === null) {
                continue;
            }

            $locale = preg_match('/\p{Bengali}/u', (string) $setting->value) === 1 ? 'bn' : 'en';

            DB::table('settings')->updateOrInsert(
                ['key' => "{$key}_{$locale}"],
                ['value' => $setting->value, 'created_at' => now(), 'updated_at' => now()],
            );

            DB::table('settings')->where('id', $setting->id)->delete();
        }

        Cache::forget('site_settings');
    }

    /**
     * Reverse the migrations, keeping the English value where both exist.
     */
    public function down(): void
    {
        foreach ($this->translatableKeys as $key) {
            $value = DB::table('settings')->where('key', "{$key}_en")->value('value')
                ?? DB::table('settings')->where('key', "{$key}_bn")->value('value');

            if ($value !== null) {
                DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'created_at' => now(), 'updated_at' => now()]);
            }

            DB::table('settings')->whereIn('key', ["{$key}_en", "{$key}_bn"])->delete();
        }

        Cache::forget('site_settings');
    }
};
