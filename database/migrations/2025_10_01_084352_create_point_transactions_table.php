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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['earn', 'redeem', 'expire', 'adjustment']);
            $table->string('description')->nullable();
            $table->integer('points_amount');
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->enum('reference_type', ['transaction', 'voucher', 'admin_adjustment', 'point_rule', 'other']);
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('cascade');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('point_rule_id')->nullable()->constrained('point_rules')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
