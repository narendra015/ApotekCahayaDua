<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Unit;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('category_id')->constrained('units')->onDelete('cascade');
        });

        // Setelah kolom unit_id ditambahkan, update produk yang masih NULL
        $defaultUnit = Unit::first();
        if ($defaultUnit) {
            DB::table('products')->update(['unit_id' => $defaultUnit->id]);
        }
    }

    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
