<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Buat tabel baru tanpa kolom product_id
        Schema::create('transactions_new', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Copy data dari tabel lama ke tabel baru
        DB::statement('INSERT INTO transactions_new (id, date, customer_id, created_at, updated_at) SELECT id, date, customer_id, created_at, updated_at FROM transactions');

        // Hapus tabel lama
        Schema::dropIfExists('transactions');

        // Rename tabel baru menjadi transactions
        Schema::rename('transactions_new', 'transactions');
    }

    public function down(): void
    {
        // Buat ulang tabel transactions dengan kolom product_id
        Schema::create('transactions_old', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained();
            $table->integer('qty')->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->timestamps();
        });

        // Copy data kembali ke tabel lama
        DB::statement('INSERT INTO transactions_old (id, date, customer_id, created_at, updated_at) SELECT id, date, customer_id, created_at, updated_at FROM transactions');

        // Hapus tabel baru
        Schema::dropIfExists('transactions');

        // Rename tabel lama menjadi transactions
        Schema::rename('transactions_old', 'transactions');
    }
};