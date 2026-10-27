<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductPrice;
use App\Repositories\Contracts\ProductPriceRepositoryInterface;

class ProductPriceRepository extends BaseRepository implements ProductPriceRepositoryInterface
{
    public function __construct(ProductPrice $model) {
        parent::__construct($model);
    }
}