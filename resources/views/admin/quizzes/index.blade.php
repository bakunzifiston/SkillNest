@extends('layouts.admin')

@section('title', 'Quizzes')
@section('header', 'Quizzes & Assessments')

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <p class="text-sm text-slate-500">Create quizzes, add questions, and set a passing grade.</p>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.quiz-results.index') }}" class="admin-btn-secondary">View results</a>
            <a href="{{ route('admin.quiz-results.export') }}" class="admin-btn-secondary">Export CSV</a>
            <a href="{{ route('admin.quizzes.create') }}" class="admin-btn-accent">Create quiz</a>
        </div>
    </div>
    <div class="mb-4">
        <form method="get">
            <select name="course_id" onchange="this.form.submit()" class="rounded-xl border-slate-300 text-sm">
                <option value="">All courses</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Quiz</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Passing grade</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Questions</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $quiz->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $quiz->course->title ?? '—' }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $quiz->passing_grade }}%</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $quiz->questions()->count() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.quizzes.questions.index', $quiz) }}" class="admin-btn-secondary">Questions</a>
                                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="post" onsubmit="return confirm('Delete this quiz and all its questions?');">
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
                                <p class="text-sm text-slate-500 mb-3">No quizzes yet.</p>
                                <a href="{{ route('admin.quizzes.create') }}" class="admin-btn-accent">Create quiz</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quizzes->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $quizzes->links() }}</div>
        @endif
    </div>
@endsection
