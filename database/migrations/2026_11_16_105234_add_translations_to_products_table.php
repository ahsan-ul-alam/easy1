<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Give products a Bengali and an English name and description.
 * Existing content is kept: text written in Bengali script becomes the Bengali version,
 * anything else becomes the English version. IDs, slugs and URLs are unchanged.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('name', 'name_en');
            $table->renameColumn('description', 'description_en');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name_en')->nullable()->change();
            $table->string('name_bn')->nullable()->after('id');
            $table->text('description_bn')->nullable()->after('slug');
        });

        DB::table('products')->orderBy('id')->chunkById(100, function ($products) {
            foreach ($products as $product) {
                $changes = [];

                if ($this->isBengali($product->name_en)) {
                    $changes += ['name_bn' => $product->name_en, 'name_en' => null];
                }

                if ($this->isBengali($product->description_en)) {
                    $changes += ['description_bn' => $product->description_en, 'description_en' => null];
                }

                if ($changes !== []) {
                    DB::table('products')->where('id', $product->id)->update($changes);
                }
            }
        });
    }

    /**
     * Reverse the migrations. Bengali-only content is moved back into the single name/description columns.
     */
    public function down(): void
    {
        DB::table('products')->whereNull('name_en')->update(['name_en' => DB::raw('name_bn')]);
        DB::table('products')->whereNull('description_en')->update(['description_en' => DB::raw('description_bn')]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_bn', 'description_bn']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name_en')->nullable(false)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('name_en', 'name');
            $table->renameColumn('description_en', 'description');
        });
    }

    private function isBengali(?string $text): bool
    {
        return $text !== null && preg_match('/\p{Bengali}/u', $text) === 1;
    }
};
