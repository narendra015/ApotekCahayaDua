<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Atribut yang bisa diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = ['date', 'customer_id', 'total_amount'];

    /**
     * Relasi ke tabel transaction_details (jika transaksi memiliki banyak produk).
     *
     * @return HasMany
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Relasi ke tabel customers.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Hitung total transaksi berdasarkan detail transaksi.
     *
     * @return int
     */
    public function calculateTotal(): int
    {
        return $this->details->sum('total');
    }
}
