<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Quiz $quiz): View
    {
        $quiz->load(['questions.options', 'course']);
        return view('admin.questions.index', compact('quiz'));
    }

    public function create(Quiz $quiz): View
    {
        $quiz->load('course');
        return view('admin.questions.create', compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:mcq,true_false',
            'question_text' => 'required|string|max:2000',
            'points' => 'required|integer|min:1|max:100',
            'true_false_correct' => 'required_if:type,true_false|nullable|in:true,false',
            'tr_new_options' => 'nullable|array',
            'tr_new_options.*' => 'nullable|string|max:1000',
        ] + Question::translationRules());
        $trNewOptions = $validated['tr_new_options'] ?? [];
        unset($validated['true_false_correct'], $validated['tr'], $validated['tr_new_options']);
        $validated['quiz_id'] = $quiz->id;
        $validated['sort_order'] = $quiz->questions()->max('sort_order') + 1;

        if ($validated['type'] === 'mcq') {
            // Keep each option's original form position so the chosen correct
            // radio still points at the right text after blank options are dropped.
            $rawOptions = array_map(fn ($t) => trim((string) $t), $request->input('options', []));
            $options = array_values(array_filter($rawOptions, fn ($t) => $t !== ''));
            // Optional translations stay aligned with their option (same form position).
            $trOptions = array_values(array_intersect_key($trNewOptions, array_filter($rawOptions, fn ($t) => $t !== '')));
            if (count($options) < 2) {
                return redirect()->back()->withInput()->withErrors(['options' => __('MCQ must have at least 2 options.')]);
            }
            $chosen = (int) $request->input('correct_option', 0);
            if (($rawOptions[$chosen] ?? '') === '') {
                return redirect()->back()->withInput()->withErrors(['options' => __('The correct option must not be blank.')]);
            }
            $correctIndex = array_search($chosen, array_keys(array_filter($rawOptions, fn ($t) => $t !== '')), true);
        }

        $question = Question::create($validated);
        $question->saveTranslations($request->input('tr', []));

        if ($validated['type'] === 'true_false') {
            $correct = $request->input('true_false_correct') === 'true';
            $trueOption = QuestionOption::create(['question_id' => $question->id, 'option_text' => 'True', 'is_correct' => $correct, 'sort_order' => 0]);
            $falseOption = QuestionOption::create(['question_id' => $question->id, 'option_text' => 'False', 'is_correct' => !$correct, 'sort_order' => 1]);
            $trueOption->saveTranslations(['rw' => ['option_text' => 'Ukuri']]);
            $falseOption->saveTranslations(['rw' => ['option_text' => 'Ikinyoma']]);
        } else {
            foreach ($options as $i => $text) {
                $option = QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $text,
                    'is_correct' => $i === $correctIndex,
                    'sort_order' => $i,
                ]);
                $option->saveTranslations(['rw' => ['option_text' => $trOptions[$i] ?? null]]);
            }
        }

        return redirect()->route('admin.quizzes.questions.index', $quiz)->with('success', __('Question added.'));
    }

    public function edit(Question $question): View
    {
        $question->load(['quiz.course', 'options']);
        return view('admin.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => 'required|string|max:2000',
            'points' => 'required|integer|min:1|max:100',
            'tr_options' => 'nullable|array',
            'tr_options.*' => 'nullable|string|max:1000',
            'tr_new_options' => 'nullable|array',
            'tr_new_options.*' => 'nullable|string|max:1000',
        ] + Question::translationRules());
        $trOptions = $validated['tr_options'] ?? [];
        $trNewOptions = $validated['tr_new_options'] ?? [];
        unset($validated['tr'], $validated['tr_options'], $validated['tr_new_options']);
        $question->update($validated);
        $question->saveTranslations($request->input('tr', []));

        if ($question->type === 'true_false') {
            $correct = $request->input('true_false_correct') === 'true';
            $opts = $question->options()->orderBy('sort_order')->get();
            if ($opts->count() >= 2) {
                $opts[0]->update(['option_text' => 'True', 'is_correct' => $correct]);
                $opts[1]->update(['option_text' => 'False', 'is_correct' => !$correct]);
            }
        } else {
            $optionIds = $request->input('option_ids', []);
            $optionTexts = $request->input('option_texts', []);
            $correctIndex = (int) $request->input('correct_option', 0);
            foreach ($optionIds as $i => $id) {
                $opt = $question->options()->find($id);
                if ($opt) {
                    $opt->update([
                        'option_text' => $optionTexts[$i] ?? $opt->option_text,
                        'is_correct' => $i === $correctIndex,
                    ]);
                    if (array_key_exists($id, $trOptions)) {
                        $opt->saveTranslations(['rw' => ['option_text' => $trOptions[$id]]]);
                    }
                }
            }
            // New options (no id)
            $newTexts = $request->input('new_options', []);
            foreach ($newTexts as $i => $text) {
                if (trim((string) $text) !== '') {
                    $newOption = QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $text,
                        'is_correct' => false,
                        'sort_order' => $question->options()->max('sort_order') + 1 + $i,
                    ]);
                    if (filled($trNewOptions[$i] ?? null)) {
                        $newOption->saveTranslations(['rw' => ['option_text' => $trNewOptions[$i]]]);
                    }
                }
            }
        }

        return redirect()->route('admin.quizzes.questions.index', $question->quiz)->with('success', __('Question updated.'));
    }

    public function destroy(Question $question): RedirectResponse
    {
        $quiz = $question->quiz;
        $question->delete();
        return redirect()->route('admin.quizzes.questions.index', $quiz)->with('success', __('Question deleted.'));
    }
}
