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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('grand_total', 15, 2)->nullable()->default(0);
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('tax', 15, 2)->nullable();
            $table->decimal('shipping', 15, 2)->nullable()->default(0);
            $table->string('shipping_method')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->enum('status', ['new', 'processing', 'shipped', 'delivered', 'cancelled'])->default('new');
            $table->string('currency')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
