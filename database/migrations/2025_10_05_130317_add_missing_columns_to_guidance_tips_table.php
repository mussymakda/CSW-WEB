<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guidance_tips', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('image')->nullable()->after('name');
            $table->string('link')->nullable()->after('image');
            $table->boolean('is_active')->default(true)->after('link');
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guidance_tips', function (Blueprint $table) {
            $table->dropColumn(['name', 'image', 'link', 'is_active', 'sort_order']);
        });
    }
};
