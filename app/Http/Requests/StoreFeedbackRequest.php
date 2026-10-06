<?php

namespace App\Http\Requests;

use App\Enums\FeedbackCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email:rfc|max:254',
            'rating' => 'required|integer|between:1,5',
            'category' => ['required', Rule::enum(FeedbackCategory::class)],
            'comment' => 'nullable|string|max:1000',
        ];
    }
}
