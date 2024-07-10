<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\Response;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function submit(Request $request, $surveyId)
    {
        $survey = Survey::findOrFail($surveyId);

        $validatedData = $request->validate([
            'responses' => 'required|array|min:1',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.answer' => 'required|string',
        ]);

        foreach ($validatedData['responses'] as $responseData) {
            Response::create([
                'survey_id' => $survey->id,
                'question_id' => $responseData['question_id'],
                'answer' => $responseData['answer'],
            ]);
        }

        return response()->json(['message' => 'Responses submitted successfully'], 201);
    }

    public function results($surveyId)
    {
        $survey = Survey::with(['questions.responses', 'questions.options'])->findOrFail($surveyId);

        $results = $survey->questions->map(function ($question) {
            if ($question->type === 'rating') {
                $ratings = $question->responses->pluck('answer')->map(function ($answer) {
                    return (int) $answer;
                });

                return [
                    'question' => $question->text,
                    'type' => 'rating',
                    'average' => $ratings->avg(),
                    'distribution' => $ratings->countBy()->sortKeys(),
                ];
            } else {
                return [
                    'question' => $question->text,
                    'type' => 'text',
                    'answers' => $question->responses->pluck('answer'),
                ];
            }
        });

        return response()->json(['results' => $results]);
    }
}