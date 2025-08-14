<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Pakai ENUM agar nilai konsisten; bisa diganti string jika perlu
            $table->enum('drug_class', [
                'obat_bebas',
                'obat_bebas_terbatas',
                'obat_keras',
                'obat_narkotika',
                'obat_herbal',
                'obat_herbal_terstandar',
                'fitofarmaka',
            ])->nullable()->after('unit_id'); // set nullable jika punya data lama
            $table->index('drug_class');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['drug_class']);
            $table->dropColumn('drug_class');
        });
    }
};
