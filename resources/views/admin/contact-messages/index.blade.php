@extends('layouts.admin')

@section('title', 'Contact messages')
@section('header', 'Contact messages')

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-sm text-slate-600">
            @if(($unreadCount ?? 0) > 0)
                <span class="inline-flex items-center gap-1.5 font-medium text-navy">
                    <span class="h-2 w-2 rounded-full bg-accent"></span>
                    {{ number_format($unreadCount) }} unread
                </span>
            @else
                All messages have been read.
            @endif
        </p>
        @adminCan('contact_messages', 'delete')
            @if(($readCount ?? 0) > 0)
                <form action="{{ route('admin.contact-messages.destroy-all') }}" method="post" onsubmit="return confirm('Delete all read messages?');">
                    @csrf
                    <button type="submit" class="admin-btn-secondary">Delete read messages</button>
                </form>
            @endif
        @endadminCan
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-success-light text-success-darker border border-success-muted text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">From</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Subject</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Received</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($messages as $message)
                        <tr class="hover:bg-slate-50/70 {{ $message->read_at ? '' : 'bg-primary-light/40' }}">
                            <td class="px-4 py-3">
                                <p class="font-medium text-navy {{ $message->read_at ? '' : 'font-semibold' }}">{{ $message->name }}</p>
                                <a href="mailto:{{ $message->email }}" class="text-xs text-slate-500 hover:text-primary truncate block max-w-[16rem]">{{ $message->email }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <span class="line-clamp-1">{{ $message->subject ?: '(No subject)' }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap tabular-nums">
                                {{ $message->created_at?->format('M j, Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($message->read_at)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">Read</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium">Unread</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.contact-messages.show', $message) }}" class="admin-btn-secondary">View</a>
                                    @adminCan('contact_messages', 'delete')
                                        <form action="{{ route('admin.contact-messages.destroy', $message) }}" method="post" onsubmit="return confirm('Delete this message?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-danger">Delete</button>
                                        </form>
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">
                                No contact messages yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($messages->hasPages())
        <div class="mt-4">
            {{ $messages->links() }}
        </div>
    @endif
@endsection
