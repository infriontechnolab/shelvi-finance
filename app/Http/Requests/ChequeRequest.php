<?php

namespace App\Http\Requests;

use App\Models\Bank;
use App\Models\Party;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChequeRequest extends FormRequest
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
            'no' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
            'party' => ['required', Rule::exists('parties', 'name')],
            'bank' => ['required', Rule::exists('banks', 'name')],
            'issue' => ['required', 'date'],
            'deposit' => ['nullable', 'date'],
            'due' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(config('options.cheque_statuses')))],
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
            'cheque_no' => $this->input('no'),
            'party_id' => Party::idForName($this->input('party')),
            'bank_id' => Bank::idForName($this->input('bank')),
            'amount' => (int) round(((float) $this->input('amount')) * 100),
            'issue_date' => $this->input('issue'),
            'deposit_date' => $this->input('deposit') ?: null,
            'due_date' => $this->input('due'),
            'status' => $this->input('status'),
        ];
    }
}
