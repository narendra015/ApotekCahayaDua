<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('order_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Relasi dengan tabel orders
        $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Relasi dengan tabel products
        $table->integer('quantity'); // Kuantitas produk yang dipesan
        $table->decimal('price', 10, 2); // Harga per unit produk
        $table->decimal('total', 15, 2);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
