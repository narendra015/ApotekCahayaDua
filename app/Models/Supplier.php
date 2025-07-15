<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    /**
     * Relationship with Category
     *
     * If suppliers belong to a category (adjust if necessary).
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship with Product (optional)
     *
     * Assuming a supplier might provide products (adjust accordingly if needed).
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
