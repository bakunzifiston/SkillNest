@extends('layouts.admin')

@section('title', 'Quizzes')
@section('header', 'Quizzes & Assessments')

@section('content')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <div class="flex items-center gap-3">
            <p class="text-gray-600">Create quizzes, add questions (MCQ / True–False), set passing grade.</p>
            <a href="{{ route('admin.quiz-results.index') }}" class="text-amber-600 hover:underline">View results</a>
            <a href="{{ route('admin.quiz-results.export') }}" class="text-amber-600 hover:underline">Export CSV</a>
        </div>
        <a href="{{ route('admin.quizzes.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Create quiz</a>
    </div>
    <div class="mb-4">
        <form method="get" class="flex gap-2 items-center">
            <select name="course_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                <option value="">All courses</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quiz</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passing grade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Questions</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($quizzes as $quiz)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $quiz->title }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $quiz->course->title ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $quiz->passing_grade }}%</td>
                    <td class="px-6 py-4 text-gray-500">{{ $quiz->questions()->count() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.quizzes.questions.index', $quiz) }}" class="text-amber-600 hover:underline mr-3">Questions</a>
                        <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-amber-600 hover:underline mr-3">Edit</a>
                        <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="post" class="inline" onsubmit="return confirm('Delete this quiz and all its questions?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No quizzes yet. <a href="{{ route('admin.quizzes.create') }}" class="text-amber-600 hover:underline">Create one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($quizzes->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $quizzes->links() }}</div>
        @endif
    </div>
@endsection
