<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canUseChat();
    }

    public function rules(): array
    {
        $conversationId = $this->route('conversation')?->id;

        return [
            'message' => ['nullable', 'string', 'max:10000', 'required_without:attachments'],
            'reply_to_message_id' => [
                'nullable',
                'integer',
                Rule::exists('messages', 'id')->where(fn ($query) => $query
                    ->where('conversation_id', $conversationId)
                    ->whereNull('deleted_at')),
            ],
            'type' => ['sometimes', 'string', Rule::in(['text', 'image', 'file', 'mixed'])],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => [
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,webp,pdf,docx,xlsx,zip,txt',
            ],
        ];
    }
}
