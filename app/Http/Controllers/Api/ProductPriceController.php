<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductPriceRequest;
use App\Http\Resources\ProductPriceResource;
use App\Models\Currency;
use App\Models\Product;
use App\Repositories\Contracts\ProductPriceRepositoryInterface;
use App\Services\PriceConverter;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Product prices')]
class ProductPriceController extends Controller
{
    public function __construct(
        private ProductPriceRepositoryInterface $productPrices,
        private PriceConverter $priceConverter,
    ) {}

    /**
     * List the prices of a product in other currencies.
     */
    public function index(Product $product): AnonymousResourceCollection
    {
        $productPrices = $this->productPrices->forProduct($product);

        return ProductPriceResource::collection($productPrices);
    }

    /**
     * Add a price for a product in another currency.
     *
     * If `price` is not sent it is calculated from the exchange rates.
     */
    public function store(StoreProductPriceRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();

        // price is optional, if missing we convert it from the product base price
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
