@extends('layouts.admin')

@section('title', 'Questions')
@section('header', 'Questions: ' . $quiz->title)

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.quizzes.index') }}" class="text-amber-600 hover:underline">← Quizzes</a>
            <span class="text-gray-500">|</span>
            <span class="text-sm text-gray-600">Course: {{ $quiz->course->title }}</span>
            <span class="text-sm text-gray-600">Passing grade: {{ $quiz->passing_grade }}%</span>
        </div>
        <a href="{{ route('admin.quizzes.questions.create', $quiz) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add question</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Question</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($quiz->questions as $index => $q)
                <tr>
                    <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-6 py-4">
                        <span class="font-medium text-gray-900">{{ Str::limit($q->question_text, 60) }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $q->type === 'mcq' ? 'MCQ' : 'True/False' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $q->points }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.questions.edit', $q) }}" class="text-amber-600 hover:underline mr-3">Edit</a>
                        <form action="{{ route('admin.questions.destroy', $q) }}" method="post" class="inline" onsubmit="return confirm('Delete this question?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No questions yet. <a href="{{ route('admin.quizzes.questions.create', $quiz) }}" class="text-amber-600 hover:underline">Add one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
