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
        Schema::create('pops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('frame_size'); // A5, A4, A3
            $table->string('layout_type'); // single_price, was_is_price, discount_percent, double_item
            $table->string('header_text')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('product_desc')->nullable();
            $table->string('sku')->nullable();
            $table->string('unit')->default('PCS');
            $table->integer('qty_print')->default(1);
            $table->string('primary_price')->nullable();
            $table->string('secondary_price')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pops');
    }
};
