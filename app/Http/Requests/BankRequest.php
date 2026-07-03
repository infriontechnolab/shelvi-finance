<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankRequest extends FormRequest
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
            // Unique among ACTIVE banks only — a soft-deleted bank's number is
            // free to reuse (its row is hidden). Ignore self on edit.
            'account' => [
                'required', 'string', 'max:34',
                Rule::unique('banks', 'account_number')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('bank')),
            ],
            'type' => ['required', Rule::in(array_keys(config('options.bank_types')))],
            'holder' => ['required', 'string', 'max:255'],
            'balance' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Map to model columns. `balance` edits the opening balance; the running
     * balance is derived from transactions. Initials default to the name's.
     *
     * @return array<string, mixed>
     */
    public function toModel(): array
    {
        $name = $this->input('name');
        $initials = mb_strtoupper(mb_substr(preg_replace('/\s+/', '', $name), 0, 2));

        return [
            'name' => $name,
            'account_number' => $this->input('account'),
            'type' => $this->input('type'),
            'holder' => $this->input('holder'),
            'initials' => $initials,
            'opening_balance' => (int) round(((float) $this->input('balance', 0)) * 100),
        ];
    }
}
