<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    // ================================
    //        MASS ASSIGNABLE
    // ================================
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
    ];

    // ================================
    //            CASTING
    // ================================
    protected $casts = [
        'price' => 'decimal:2',
        'expired_date' => 'datetime',
    ];

    // ================================
    //         RELATIONSHIPS
    // ================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(ProductStockHistory::class)->orderBy('created_at', 'asc');
    }

    // ================================
    //        LOGIKA FIFO STOK
    // ================================

    /**
     * Ambil histori stok tertua (untuk tampilan FIFO).
     */
    public function oldestStock()
    {
        return $this->stockHistories()->orderBy('created_at', 'asc')->first();
    }

    /**
     * Perbarui harga & tanggal kadaluarsa utama dari stok yang masih tersedia (FIFO).
     */
    public function updateProductWithFIFO(): void
    {
        $nextStock = $this->stockHistories()
            ->where('qty', '>', 0)
            ->orderBy('expired_date')
            ->first();

        if ($nextStock) {
            $this->price = $nextStock->price;
            $this->expired_date = $nextStock->expired_date;
        } else {
            $this->price = 0;
            $this->expired_date = null;
        }

        $this->save();
    }

    /**
     * Kurangi stok produk sesuai urutan FIFO.
     *
     * @param int $qtyToReduce Jumlah stok yang akan dikurangi
     */
    public function reduceStock(int $qtyToReduce): void
    {
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

        // Update stok total di tabel produk
        $this->qty -= $qtyToReduce;
        if ($this->qty < 0) $this->qty = 0;
        $this->save();

        // Update price dan expired dari batch stok selanjutnya (FIFO)
        $this->updateProductWithFIFO();
    }
}
