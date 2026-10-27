<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductPriceRepositoryInterface extends BaseRepositoryInterface
{
    public function forProduct(Product $product): Collection;
}
