<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response as HttpResponse;

#[Group('Products')]
class ProductController extends Controller
{
    public function __construct(private ProductRepositoryInterface $products) {}

    /**
     * List products (paginated, 15 per page).
     */
    public function index(): AnonymousResourceCollection
    {
        $products = $this->products->paginate(15, ['currency']);

        return ProductResource::collection($products);
    }

    /**
     * Get a product with its currency and prices.
     */
    public function show(Product $product): ProductResource
    {
        $product->load('currency', 'prices.currency');

        return new ProductResource($product);
    }

    /**
     * Create a product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());
        $product->load('currency');

        return (new ProductResource($product))->response()
            ->setStatusCode(201);
    }

    /**
     * Update a product. Only the fields sent are changed.
     */
    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $this->products->update($product, $request->validated());
        $product->load('currency');

        return new ProductResource($product);
    }

    /**
     * Delete a product and its prices.
     */
    public function destroy(Product $product): HttpResponse
    {
        $this->products->delete($product);

        return response()->noContent();
    }
}
