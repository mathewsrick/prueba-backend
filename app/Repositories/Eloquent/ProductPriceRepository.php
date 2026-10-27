<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Repositories\Contracts\ProductPriceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductPriceRepository extends BaseRepository implements ProductPriceRepositoryInterface
{
    public function __construct(ProductPrice $model)
    {
        parent::__construct($model);
    }

    public function forProduct(Product $product): Collection
    {
        return $product->prices()->with('currency')->get();
    }
}
