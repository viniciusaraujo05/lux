<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExplanationFeedbackController extends Controller
{
    /**
     * Store a new feedback for a Bible explanation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'bible_explanation_id' => 'required|exists:bible_explanations,id',
            'is_positive' => 'required|boolean',
            'comment' => 'nullable|string|max:1000',
            'testament' => 'required|string|max:10',
            'book' => 'required|string|max:50',
            'chapter' => 'required|integer',
            'verses' => 'nullable|string|max:100',
        ]);

        // Get the Bible explanation
        $explanation = \App\Models\BibleExplanation::findOrFail($request->bible_explanation_id);

        // Create the feedback
        $feedback = new \App\Models\ExplanationFeedback([
            'bible_explanation_id' => $request->bible_explanation_id,
            'is_positive' => $request->is_positive,
            'comment' => $request->comment,
            'testament' => $request->testament,
            'book' => $request->book,
            'chapter' => $request->chapter,
            'verses' => $request->verses,
            'user_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $feedback->save();

        // Update the feedback count on the explanation
        if ($request->is_positive) {
            $explanation->incrementPositiveFeedback();
        } else {
            $explanation->incrementNegativeFeedback();
        }

        return response()->json([
            'success' => true,
            'message' => 'Feedback registrado com sucesso!',
            'feedback' => $feedback,
        ]);
    }

    /**
     * Get feedback statistics for a Bible explanation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats($id)
    {
        $explanation = \App\Models\BibleExplanation::findOrFail($id);
        
        $stats = [
            'positive_count' => $explanation->positive_feedback_count,
            'negative_count' => $explanation->negative_feedback_count,
            'total_count' => $explanation->positive_feedback_count + $explanation->negative_feedback_count,
        ];
        
        if ($stats['total_count'] > 0) {
            $stats['positive_percentage'] = round(($stats['positive_count'] / $stats['total_count']) * 100);
            $stats['negative_percentage'] = round(($stats['negative_count'] / $stats['total_count']) * 100);
        } else {
            $stats['positive_percentage'] = 0;
            $stats['negative_percentage'] = 0;
        }
        
        return response()->json($stats);
    }
}
