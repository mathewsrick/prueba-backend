<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Currency\StoreCurrencyRequest;
use App\Http\Requests\Currency\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Repositories\Contracts\CurrencyRepositoryInterface;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

#[Group('Currencies')]
class CurrencyController extends Controller
{
    public function __construct(private CurrencyRepositoryInterface $currencies) {}

    /**
     * List all currencies.
     */
    public function index(): AnonymousResourceCollection
    {
        return CurrencyResource::collection($this->currencies->all());
    }

    /**
     * Create a currency.
     */
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $currency = $this->currencies->create($request->validated());

        return (new CurrencyResource($currency))->response()->setStatusCode(201);
    }

    /**
     * Get a currency.
     */
    public function show(Currency $currency): CurrencyResource
    {
        return new CurrencyResource($currency);
    }

    /**
     * Update a currency.
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency): CurrencyResource
    {
        $this->currencies->update($currency, $request->validated());

        return new CurrencyResource($currency);
    }

    /**
     * Delete a currency. Returns 409 if it is still used by products or prices.
     */
    public function destroy(Currency $currency): JsonResponse|Response
    {
        // check first, otherwise the FK restriction throws a 500
        if ($currency->products()->exists() || $currency->productPrices()->exists()) {
            return response()->json([
                'message' => 'Currency is in use by products or prices and cannot be deleted.',
            ], 409);
        }

        $this->currencies->delete($currency);

        return response()->noContent();
    }
}
