<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id(); // Kolom ID utama
            $table->string('name'); // Nama supplier
            $table->string('email')->unique(); // Email supplier
            $table->string('phone'); // Nomor telepon supplier
            $table->string('address'); // Alamat supplier
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
}
