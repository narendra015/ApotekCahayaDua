<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;

return new class extends Migration {
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('unit_id')->constrained('suppliers')->onDelete('cascade');
        });

        // Setelah kolom supplier_id ditambahkan, update produk yang masih NULL
        $defaultSupplier = Supplier::first();
        if ($defaultSupplier) {
            DB::table('products')->update(['supplier_id' => $defaultSupplier->id]);
        }
    }

    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};
