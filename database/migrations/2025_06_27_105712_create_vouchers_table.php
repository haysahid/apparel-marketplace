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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->index();
            $table->string('description')->nullable();
            $table->enum('type', ['fixed', 'percentage'])->default('percentage');
            $table->integer('amount')->default(0);
            $table->integer('min_amount')->nullable();
            $table->integer('max_amount')->nullable();
            $table->timestamp('redeem_start_date')->nullable();
            $table->timestamp('redeem_end_date')->nullable();
            $table->integer('usage_duration_days')->nullable();
            $table->timestamp('usage_start_date')->nullable();
            $table->timestamp('usage_end_date')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('required_points')->nullable();
            $table->string('usage_url')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_internal')->default(true);
            $table->foreignId('partner_id')->nullable()->constrained('partners')->onDelete('set null');
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
        Schema::dropIfExists('vouchers');
    }
};
