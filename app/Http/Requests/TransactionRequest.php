<?php

namespace App\Http\Requests;

use App\Models\Bank;
use App\Models\Party;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
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
            'direction' => ['required', Rule::in(['received', 'paid'])],
            'party' => ['required', Rule::exists('parties', 'name')],
            'customer' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', Rule::in(array_keys(config('options.payment_methods')))],
            'bank' => ['required', Rule::exists('banks', 'name')],
            'ref' => ['nullable', 'string', 'max:100'],
            'remark' => ['nullable', 'string', 'max:1000'],
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(config('options.transaction_statuses')))],
        ];
    }

    /**
     * Map to model columns: resolve party/bank names → ids, rupees → paise.
     *
     * @return array<string, mixed>
     */
    public function toModel(): array
    {
        return [
            'direction' => $this->input('direction'),
            'party_id' => Party::idForName($this->input('party')),
            'customer_name' => $this->input('customer') ?: null,
            'bank_id' => Bank::idForName($this->input('bank')),
            'method' => $this->input('method'),
            'amount' => (int) round(((float) $this->input('amount')) * 100),
            'reference' => $this->input('ref') ?: null,
            'remark' => $this->input('remark') ?: null,
            'txn_date' => $this->input('date'),
            'status' => $this->input('status'),
        ];
    }
}
