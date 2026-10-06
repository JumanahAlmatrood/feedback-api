<?php

namespace App\Http\Requests;

use App\Enums\FeedbackCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', Rule::enum(FeedbackCategory::class)],
            'rating' => 'nullable|integer|between:1,5',
            'page' => 'nullable|integer|min:1',
        ];
    }
}