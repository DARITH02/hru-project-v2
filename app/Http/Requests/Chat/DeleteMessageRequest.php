<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canUseChat();
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['me', 'everyone'])],
        ];
    }
}
