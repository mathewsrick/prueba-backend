<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    /** @use HasFactory<\Database\Factories\CurrencyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'exchange_rate',
    ];

    protected function casts(): array
    {
        return ['exchange_rate' => 'decimal:3'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
