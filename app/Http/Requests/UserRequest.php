<?php

namespace App\Http\Requests;

use App\Support\Access;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('user')?->id;
        $creating = $userId === null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            // Required when creating; optional when editing (blank = keep current).
            'password' => [$creating ? 'required' : 'nullable', 'string', 'min:8'],
            'role' => ['required', Rule::exists('roles', 'name'), Rule::notIn(Access::hiddenRoles())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Unchecked checkbox → treat as inactive.
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /**
     * Model columns only (password handled separately, role via syncRoles).
     *
     * @return array<string, mixed>
     */
    public function toModel(): array
    {
        $data = [
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'is_active' => $this->boolean('is_active'),
        ];

        if (filled($this->input('password'))) {
            $data['password'] = Hash::make($this->input('password'));
        }

        return $data;
    }
}
