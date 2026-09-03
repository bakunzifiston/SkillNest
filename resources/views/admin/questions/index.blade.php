@extends('layouts.admin')

@section('title', 'Questions')
@section('header', 'Questions: ' . $quiz->title)

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.quizzes.index') }}" class="admin-btn-secondary">Quizzes</a>
            <span class="text-sm text-slate-500">{{ $quiz->course->title }} · Passing grade {{ $quiz->passing_grade }}%</span>
        </div>
        <a href="{{ route('admin.quizzes.questions.create', $quiz) }}" class="admin-btn-accent">Add question</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">#</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Question</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Points</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($quiz->questions as $index => $q)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 text-slate-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-navy">{{ Str::limit($q->question_text, 60) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $q->type === 'mcq' ? 'MCQ' : 'True/False' }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $q->points }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.questions.edit', $q) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.questions.destroy', $q) }}" method="post" onsubmit="return confirm('Delete this question?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">No questions yet.</p>
                                <a href="{{ route('admin.quizzes.questions.create', $quiz) }}" class="admin-btn-accent">Add question</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
