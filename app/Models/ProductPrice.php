<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    /** @use HasFactory<\Database\Factories\ProductPriceFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'price',
        'currency_id',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:3'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
