<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'unit_id',
        'supplier_id',
        'name',
        'description',
        'price',
        'expired_date',
        'qty',
        'image',
        'drug_class',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'expired_date' => 'datetime', // boleh 'date' jika hanya tanggal
    ];

    // ------------------------------
    // RELATIONSHIPS
    // ------------------------------
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }

    public function stockHistories(): HasMany
    {
        // default order by created_at asc agar oldestStock() konsisten
        return $this->hasMany(ProductStockHistory::class)->orderBy('created_at', 'asc');
    }

    // ------------------------------
    // FIFO & AGREGASI STOK
    // ------------------------------

    /** Histori stok tertua. */
    public function oldestStock()
    {
        return $this->stockHistories()->orderBy('created_at', 'asc')->first();
    }

    /**
     * HITUNG ULANG qty TOTAL dari seluruh histori yang qty>0.
     * Aman dipanggil setiap selesai mutasi stok.
     */
    public function recalcTotalQty(): int
    {
        $total = (int) $this->stockHistories()
            ->where('qty', '>', 0)
            ->sum('qty');

        // simpan ke kolom qty di tabel products agar sinkron
        $this->qty = $total;
        $this->save();

        return $total;
    }

    /**
     * Perbarui harga & expired dari batch FIFO yang masih tersedia
     * + sekalian hitung ulang qty total agar SELALU sinkron.
     */
    public function updateProductWithFIFO(): void
    {
        $nextStock = $this->stockHistories()
            ->where('qty', '>', 0)
            ->orderBy('expired_date') // FIFO berdasar ED
            ->first();

        if ($nextStock) {
            $this->price        = $nextStock->price;
            $this->expired_date = $nextStock->expired_date;
        } else {
            $this->price        = 0;
            $this->expired_date = null;
        }

        // >>> PERBAIKAN PENTING: sinkronkan qty total
        $this->qty = (int) $this->stockHistories()
            ->where('qty', '>', 0)
            ->sum('qty');

        $this->save();
    }

    /**
     * Kurangi stok produk sesuai urutan FIFO.
     */
    public function reduceStock(int $qtyToReduce): void
    {
        // pastikan qty terbaru sebelum validasi
        $this->recalcTotalQty();

        if ($qtyToReduce > $this->qty) {
            throw new \Exception('Stok tidak mencukupi!');
        }

        $remainingQty = $qtyToReduce;

        $stocks = $this->stockHistories()
            ->where('qty', '>', 0)
            ->orderBy('expired_date')
            ->get();

        foreach ($stocks as $stock) {
            if ($remainingQty <= 0) break;

            $deductQty = min($stock->qty, $remainingQty);
            $stock->qty -= $deductQty;
            $stock->save();

            $remainingQty -= $deductQty;
        }

        // setelah mengurangi, refresh agregat & batch aktif
        $this->updateProductWithFIFO();
    }

    // ------------------------------
    // AKSESOR / UTILITAS TAMBAHAN
    // ------------------------------
    protected const DRUG_CLASS_LABELS = [
        'obat_bebas'             => 'Obat Bebas',
        'obat_bebas_terbatas'    => 'Obat Bebas Terbatas',
        'obat_keras'             => 'Obat Keras',
        'obat_narkotika'         => 'Obat Narkotika',
        'obat_herbal'            => 'Obat Herbal',
        'obat_herbal_terstandar' => 'Obat Herbal Terstandar',
        'fitofarmaka'            => 'Fitofarmaka',
    ];

    public function getDrugClassLabelAttribute(): string
    {
        return self::DRUG_CLASS_LABELS[$this->drug_class] ?? $this->drug_class ?? '-';
    }
}
