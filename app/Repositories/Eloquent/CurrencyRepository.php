<?php

namespace App\Repositories\Eloquent;

use App\Models\Currency;
use App\Repositories\Contracts\CurrencyRepositoryInterface;

class CurrencyRepository extends BaseRepository implements CurrencyRepositoryInterface
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }
}
