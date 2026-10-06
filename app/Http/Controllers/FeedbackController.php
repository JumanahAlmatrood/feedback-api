<?php

namespace App\Http\Controllers;

use App\Enums\FeedbackCategory;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'category' => ['nullable', Rule::enum(FeedbackCategory::class)],
            'rating' => 'nullable|integer|between:1,5',
        ]);

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email:rfc|max:254',
            'rating' => 'required|integer|between:1,5',
            'category' => ['required', Rule::enum(FeedbackCategory::class)],
            'comment' => 'nullable|string|max:1000',
        ]);

        $feedback = Feedback::create($data);

        return response()->json($feedback, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        return $feedback;
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, Feedback $feedback)
    // {
    //
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return response()->noContent();
    }
}
