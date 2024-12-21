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
        Schema::create('favourite_products', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('stores_product_id');
            $table->unsignedBigInteger('user_id');

            // Foreign keys
            $table->foreign('stores_product_id')->references('id')->on('store_product')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favourite_products');
    }
};
