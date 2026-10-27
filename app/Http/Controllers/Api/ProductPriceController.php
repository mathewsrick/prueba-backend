<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductPriceRequest;
use App\Http\Resources\ProductPriceResource;
use App\Models\Currency;
use App\Models\Product;
use App\Repositories\Contracts\ProductPriceRepositoryInterface;
use App\Services\PriceConverter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductPriceController extends Controller
{
    public function __construct(
        private ProductPriceRepositoryInterface $productPrices,
        private PriceConverter $priceConverter,
    ) {}

    public function index(Product $product): AnonymousResourceCollection
    {
        $productPrices = $this->productPrices->forProduct($product);

        return ProductPriceResource::collection($productPrices);
    }

    public function store(StoreProductPriceRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();
        if (! isset($validated['price'])) {
            $currency = Currency::findOrFail($validated['currency_id']);
            $validated['price'] = $this->priceConverter->convert($product, $currency);
        }
        $validated['product_id'] = $product->id;

        $productPrice = $this->productPrices->create($validated);
        $productPrice->load('currency');

        return (new ProductPriceResource($productPrice))->response()
            ->setStatusCode(201);
    }
}
