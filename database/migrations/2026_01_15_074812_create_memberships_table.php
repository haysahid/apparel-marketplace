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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('group');
            $table->string('name');
            $table->string('alias')->nullable();
            $table->integer('level')->default(0);
            $table->string('slug')->index();
            $table->string('description')->nullable();
            $table->integer('item_discount_percentage')->default(0);
            $table->integer('shipping_discount_percentage')->default(0);
            $table->integer('min_purchase_amount')->default(0);
            $table->string('hex_code_bg')->nullable();
            $table->string('hex_code_text')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
