<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Currency\StoreCurrencyRequest;
use App\Http\Requests\Currency\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Repositories\Contracts\CurrencyRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyRepositoryInterface $currencies) {}

    public function index(): AnonymousResourceCollection
    {
        return CurrencyResource::collection($this->currencies->all());
    }

    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $currency = $this->currencies->create($request->validated());
        return (new CurrencyResource($currency))->response()->setStatusCode(201);
    }

    public function show(Currency $currency): CurrencyResource
    {
        return new CurrencyResource($currency);
    }

    public function update(UpdateCurrencyRequest $request, Currency $currency): CurrencyResource
    {
        $this->currencies->update($currency, $request->validated());

        return new CurrencyResource($currency);
    }

    public function destroy(Currency $currency): JsonResponse|Response
    {
        if ($currency->products()->exists() || $currency->productPrices()->exists()) {
            return response()->json([
                'message' => 'Currency is in use by products or prices and cannot be deleted.',
            ], 409);
        }

        $this->currencies->delete($currency);

        return response()->noContent();
    }
}
