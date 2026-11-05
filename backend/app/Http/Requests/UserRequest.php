<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
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
        $user = $this->route('user');

        $password = ['string', 'max:100', Password::min(8)->mixedCase()->numbers()];

        return [
            'role' => ['required', Rule::in(['admin', 'operator'])],
            'username' => ['required', 'string', 'min:8', 'max:100', Rule::unique('users', 'username')->ignore($user?->id)],
            'password' => $user ? ['nullable', ...$password] : ['required', ...$password],
            'name' => ['required', 'string', 'min:8', 'max:100'],
            'email' => ['required', 'email', 'min:8', 'max:100', Rule::unique('users', 'email')->ignore($user?->id)],
        ];
    }
}
