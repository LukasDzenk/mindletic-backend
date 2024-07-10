<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            // 'questions.*.text' => 'required|string',
            // 'questions.*.type' => 'required|in:rating,text',
            // 'questions.*.options' => 'required_if:questions.*.type,rating|array|min:2',
            // 'questions.*.options.*.text' => 'required_if:questions.*.type,rating|string',
            // 'questions.*.options.*.value' => 'required_if:questions.*.type,rating|integer|min:1|max:5',
        ]);

        $survey = Survey::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
        ]);

        foreach ($validatedData['questions'] as $questionData) {
            $question = $survey->questions()->create([
                'text' => $questionData['text'],
                'type' => $questionData['type'],
            ]);

            if ($questionData['type'] === 'rating') {
                foreach ($questionData['options'] as $optionData) {
                    $question->options()->create([
                        'text' => $optionData['text'],
                        'value' => $optionData['value'],
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Survey created successfully', 'survey' => $survey], 201);
    }

    public function show($id)
    {
        $survey = Survey::with('questions.options')->findOrFail($id);
        return response()->json($survey);
    }

    public function index()
    {
        $surveys = Survey::all();
        return response()->json($surveys);
    }
}