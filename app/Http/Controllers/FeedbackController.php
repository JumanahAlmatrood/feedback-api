<?php

namespace App\Http\Controllers;

use App\Enums\FeedbackCategory;
use App\Http\Requests\ListFeedbackRequest;
use App\Http\Requests\StoreFeedbackRequest;
use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function index(ListFeedbackRequest $request)
    {
        $filters = $request->validated();

        return Feedback::query()
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['rating'] ?? null, fn ($query, $rating) => $query->where('rating', $rating))
            ->latest('id')
            ->paginate(15);
    }

    /**
     * List the categories the API accepts, so the frontend never keeps its own copy.
     */
    public function categories()
    {
        return array_column(FeedbackCategory::cases(), 'value');
    }

    public function stats()
    {
        $ratings = Feedback::query()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $categories = Feedback::query()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'total' => Feedback::count(),
            'average_rating' => round((float) Feedback::avg('rating'), 2),
            'rating_distribution' => collect(range(1, 5))->map(fn ($rating) => [
                'rating' => $rating,
                'count' => (int) ($ratings[$rating] ?? 0),
            ]),
            'by_category' => $categories,
        ]);
    }

    public function store(StoreFeedbackRequest $request)
    {
        $feedback = Feedback::create($request->validated());

        return response()->json($feedback, 201);
    }

    public function show(Feedback $feedback)
    {
        return $feedback;
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return response()->noContent();
    }
}