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
            // Payee's bank details for a payment (who the money actually went to).
            'payee_holder' => ['nullable', 'string', 'max:255'],
            'payee_account' => ['nullable', 'string', 'max:34'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', Rule::in(array_keys(config('options.payment_methods')))],
            // Bank is selected by account number, not name — two banks can share a name.
            'bank' => ['required', Rule::exists('banks', 'account_number')],
            'ref' => ['nullable', 'string', 'max:100'],
            'remark' => ['nullable', 'string', 'max:1000'],
            'date' => ['required', 'date'],
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
            'payee_holder' => $this->input('payee_holder') ?: null,
            'payee_account_no' => $this->input('payee_account') ?: null,
            'bank_id' => Bank::idForAccount($this->input('bank')),
            'method' => $this->input('method'),
            'amount' => (int) round(((float) $this->input('amount')) * 100),
            'reference' => $this->input('ref') ?: null,
            'remark' => $this->input('remark') ?: null,
            'txn_date' => $this->input('date'),
            // No status field in the UI — every receipt/payment posts as Cleared.
            'status' => 'Cleared',
        ];
    }
}
