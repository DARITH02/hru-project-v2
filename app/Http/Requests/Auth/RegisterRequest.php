<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->email)),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isTeacherInvite = $this->input('role') === 'teacher' && $this->hasValidSignature();

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc|unique:users,email',
            'email_otp' => 'required|digits:6',
            'password' => 'required|min:8|confirmed',
            'role' => 'nullable|in:teacher',
            'admin_key' => 'nullable|string',
            'phone' => $isTeacherInvite ? 'required|string|max:50' : 'nullable|string|max:50',
            'department_id' => $isTeacherInvite ? 'nullable|integer|exists:departments,id' : 'nullable',
            'specialization' => $isTeacherInvite ? 'nullable|string|max:255' : 'nullable',
        ];
    }
}
