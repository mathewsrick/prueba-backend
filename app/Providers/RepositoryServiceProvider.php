<?php

namespace App\Providers;

use App\Repositories\Contracts\CurrencyRepositoryInterface;
use App\Repositories\Contracts\ProductPriceRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\CurrencyRepository;
use App\Repositories\Eloquent\ProductPriceRepository;
use App\Repositories\Eloquent\ProductRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CurrencyRepositoryInterface::class, CurrencyRepository::class);
        $this->app->bind(ProductPriceRepositoryInterface::class, ProductPriceRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
