<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route-level permission middleware guards this
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(config('options.party_categories')))],
            'phone' => ['nullable', 'string', 'max:20'],
            'opening' => ['nullable', 'numeric', 'min:0'],
            'balType' => ['required', Rule::in(array_keys(config('options.balance_types')))],
            'limit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_keys(config('options.party_statuses')))],
        ];
    }

    /**
     * Map validated form fields to model columns (rupees → paise; opening signed
     * by DR/CR so the derived balance matches the chosen side).
     *
     * @return array<string, mixed>
     */
    public function toModel(): array
    {
        $sign = $this->input('balType') === 'CR' ? -1 : 1;

        return [
            'name' => $this->input('name'),
            'category' => $this->input('category'),
            'phone' => $this->input('phone'),
            'opening_balance' => (int) round(((float) $this->input('opening', 0)) * 100) * $sign,
            'balance_type' => $this->input('balType'),
            'credit_limit' => (int) round(((float) $this->input('limit', 0)) * 100),
            'status' => $this->input('status'),
        ];
    }
}
