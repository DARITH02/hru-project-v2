<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class TypingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canUseChat();
    }

    public function rules(): array
    {
        return [
            'typing' => ['required', 'boolean'],
        ];
    }
}
