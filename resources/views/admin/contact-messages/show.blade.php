@extends('layouts.admin')

@section('title', 'Contact message')
@section('header', 'Contact message')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{ route('admin.contact-messages.index') }}" class="admin-btn-secondary">Back to messages</a>
        @adminCan('settings', 'delete')
            <form action="{{ route('admin.contact-messages.destroy', $message) }}" method="post" onsubmit="return confirm('Delete this message?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn-danger">Delete message</button>
            </form>
        @endadminCan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <dt class="text-sm text-slate-500">From</dt>
                <dd class="mt-1 font-medium text-navy">{{ $message->name }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Email</dt>
                <dd class="mt-1">
                    <a href="mailto:{{ $message->email }}" class="font-medium text-primary hover:underline">{{ $message->email }}</a>
                </dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Subject</dt>
                <dd class="mt-1 font-medium text-navy">{{ $message->subject ?: '(No subject)' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-slate-500">Received</dt>
                <dd class="mt-1 text-slate-700">{{ $message->created_at?->format('M j, Y H:i') ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <h3 class="px-6 py-3 bg-slate-50 font-medium text-navy border-b border-slate-200">Message</h3>
        <div class="px-6 py-5 text-slate-700 whitespace-pre-wrap leading-relaxed">{{ $message->message }}</div>
    </div>
@endsection
