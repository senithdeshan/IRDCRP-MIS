<?php

namespace App\Http\Requests;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->user()->isProtectedSuperAdmin() && is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'bail',
                'required',
                'string',
                'email',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($value)])
                        ->whereKeyNot($this->user()->id)->exists()) {
                        $fail('This email address is already assigned to another account.');
                    }
                },
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($this->user()->staffProfile && strcasecmp($value, (string) $this->user()->staffProfile->official_email) !== 0) {
                        $fail('Your email must match the official email in your registered staff profile.');
                    }
                },
            ],
        ];
    }
}
