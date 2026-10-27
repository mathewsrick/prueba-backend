<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductPriceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // optional: when empty the controller calculates it with the exchange rate
            'price' => ['nullable', 'numeric', 'min:0'],
            // a product can only have one price per currency
            'currency_id' => ['required', 'integer', 'exists:currencies,id', Rule::unique('product_prices')->where(function ($query) {
                return $query->where('product_id', $this->route('product')->id);
            })],
        ];
    }

    public function messages(): array
    {
        return ['currency_id.unique' => 'This product already has a price in that currency.'];
    }
}
