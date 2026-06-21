<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canUseChat();
    }

    public function rules(): array
    {
        return [
            'participant_ids' => ['required', 'array', 'min:1', 'max:20'],
            'participant_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['teacher', 'admin', 'super_admin'])->where('is_approved', true)),
            ],
            'type' => ['sometimes', 'string', Rule::in(['direct', 'group'])],
        ];
    }
}
