<?php

namespace App\Models;

use App\Enums\FeedbackCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'rating', 'category', 'comment'];

    /**
     * Emails are collected for follow-up only and are never returned by the API.
     *
     * @var list<string>
     */
    protected $hidden = ['email'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => FeedbackCategory::class,
        ];
    }
}
