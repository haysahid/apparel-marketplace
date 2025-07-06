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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('sku', 100)->unique()->index();
            $table->string('barcode', 100)->unique()->nullable()->index();
            $table->string('slug')->unique()->index();
            $table->string('motif', 100)->nullable();
            $table->foreignId('color_id')->nullable()->constrained('colors')->onDelete('set null');
            $table->foreignId('size_id')->nullable()->constrained('sizes')->onDelete('set null');
            $table->string('material', 100)->nullable();
            $table->integer('purchase_price')->unsigned()->default(0);
            $table->integer('base_selling_price')->unsigned()->default(0);
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->integer('discount')->unsigned()->default(0);
            $table->integer('final_selling_price')->unsigned()->default(0);
            $table->integer('current_stock_level')->default(0);
            $table->timestamp('last_stock_update')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->integer('min_order')->unsigned()->default(1);
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
