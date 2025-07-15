<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdatePhoneColumnInSuppliersTable extends Migration
{
    public function up()
    {
        // Langkah 1: Membuat tabel baru sementara dengan tipe data yang benar
        Schema::create('suppliers_temp', function (Blueprint $table) {
            $table->id(); // Kolom ID utama
            $table->string('name'); // Nama supplier
            $table->string('email')->unique(); // Email supplier
            $table->string('phone'); // Kolom phone yang baru dengan tipe integer
            $table->string('address'); // Alamat supplier
            $table->timestamps(); // Kolom created_at dan updated_at
        });

        // Langkah 2: Salin data dari tabel lama ke tabel baru
        DB::statement('INSERT INTO suppliers_temp (id, name, email, phone, address, created_at, updated_at) SELECT id, name, email, phone, address, created_at, updated_at FROM suppliers');

        // Langkah 3: Hapus tabel lama
        Schema::dropIfExists('suppliers');

        // Langkah 4: Ganti nama tabel baru menjadi nama tabel lama
        Schema::rename('suppliers_temp', 'suppliers');
    }

    public function down()
    {
        // Jika rollback, kamu bisa mengembalikan proses ke struktur sebelumnya
        Schema::create('suppliers_temp', function (Blueprint $table) {
            $table->id(); // Kolom ID utama
            $table->string('name'); // Nama supplier
            $table->string('email')->unique(); // Email supplier
            $table->integer('phone'); // Kolom phone dengan tipe string
            $table->string('address'); // Alamat supplier
            $table->timestamps(); // Kolom created_at dan updated_at
        });

        // Salin data kembali dari tabel baru ke tabel lama
        DB::statement('INSERT INTO suppliers_temp (id, name, email, phone, address, created_at, updated_at) SELECT id, name, email, phone, address, created_at, updated_at FROM suppliers');

        // Hapus tabel yang baru dibuat
        Schema::dropIfExists('suppliers');

        // Ganti nama tabel temp ke nama tabel lama
        Schema::rename('suppliers_temp', 'suppliers');
    }
}
