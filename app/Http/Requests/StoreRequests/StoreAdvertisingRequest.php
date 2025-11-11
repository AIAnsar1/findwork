<?php

namespace App\Http\Requests\StoreRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvertisingRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'array'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'language' => ['required', 'string', 'max:5'],
            'published_at' => ['required', 'date'],
            'expires_at' => ['required', 'date'],
            'status' => ['required', 'string', 'max:255'],
            'telegram_post_id' => ['required', 'string', 'max:255'],
            'post_url' => ['required', 'string', 'max:255'],
            'link' => ['required', 'string', 'max:255'],
            'views' => ['required', 'integer'],
            'reactions_count' => ['required', 'integer'],
            'channels' => ['required', 'array'],
            'groups' => ['required', 'array'],
            'tags' => ['required', 'array'],
        ];
    }
}
