<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Feedback::latest()->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
        public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'rating' => 'required|integer|between:1,5',
            'category' => 'required|string|max:100',
            'comment' => 'nullable|string',
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
    //public function update(Request $request, Feedback $feedback)
    //{
        //
    //}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return response()->noContent();
    }
}
