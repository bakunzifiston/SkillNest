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
        ]);
        $validated['quiz_id'] = $quiz->id;
        $validated['sort_order'] = $quiz->questions()->max('sort_order') + 1;

        if ($validated['type'] === 'mcq') {
            $options = array_values(array_filter(array_map(fn ($t) => trim((string) $t), $request->input('options', []))));
            if (count($options) < 2) {
                return redirect()->back()->withInput()->withErrors(['options' => 'MCQ must have at least 2 options.']);
            }
            $correctIndex = min((int) $request->input('correct_option', 0), count($options) - 1);
        }

        $question = Question::create($validated);

        if ($validated['type'] === 'true_false') {
            $correct = $request->input('true_false_correct') === 'true';
            QuestionOption::create(['question_id' => $question->id, 'option_text' => 'True', 'is_correct' => $correct, 'sort_order' => 0]);
            QuestionOption::create(['question_id' => $question->id, 'option_text' => 'False', 'is_correct' => !$correct, 'sort_order' => 1]);
        } else {
            foreach ($options as $i => $text) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $text,
                    'is_correct' => $i === $correctIndex,
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('admin.quizzes.questions.index', $quiz)->with('success', 'Question added.');
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
        ]);
        $question->update($validated);

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
                }
            }
            // New options (no id)
            $newTexts = $request->input('new_options', []);
            foreach ($newTexts as $i => $text) {
                if (trim((string) $text) !== '') {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $text,
                        'is_correct' => false,
                        'sort_order' => $question->options()->max('sort_order') + 1 + $i,
                    ]);
                }
            }
        }

        return redirect()->route('admin.quizzes.questions.index', $question->quiz)->with('success', 'Question updated.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $quiz = $question->quiz;
        $question->delete();
        return redirect()->route('admin.quizzes.questions.index', $quiz)->with('success', 'Question deleted.');
    }
}
