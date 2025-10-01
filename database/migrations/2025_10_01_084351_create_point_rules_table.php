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
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', [
                'per_amount_spent',
                'per_transaction',
                'on_signup',
                'on_review',
                'on_referral',
                'on_birthday',
                'on_anniversary',
                'other'
            ]);
            $table->integer('min_spend')->nullable();
            $table->integer('points_earned');
            $table->integer('conversion_rate')->nullable();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_rules');
    }
};
