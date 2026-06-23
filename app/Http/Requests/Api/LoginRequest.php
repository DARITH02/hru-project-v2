<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required_without_all:login,phone,student_code',
            'login' => 'required_without_all:email,phone,student_code',
            'phone' => 'required_without_all:email,login,student_code',
            'password' => 'required',
            'role' => 'nullable|string|in:teacher,student',
            'student_code' => 'nullable|string',
            'device_name' => 'nullable|string|max:255',
        ];
    }

    public function loginIdentifier(): string
    {
        return trim((string) ($this->email ?? $this->login ?? $this->phone));
    }
}
