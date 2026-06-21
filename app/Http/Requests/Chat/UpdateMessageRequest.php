<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canUseChat();
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:10000'],
        ];
    }
}
