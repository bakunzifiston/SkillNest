<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = ContactMessage::query()->whereNull('read_at')->count();
        $readCount = ContactMessage::query()->whereNotNull('read_at')->count();

        return view('admin.contact-messages.index', compact('messages', 'unreadCount', 'readCount'));
    }

    public function show(ContactMessage $contact_message): View
    {
        if (! $contact_message->read_at) {
            $contact_message->update(['read_at' => now()]);
        }

        return view('admin.contact-messages.show', [
            'message' => $contact_message,
        ]);
    }

    public function destroy(ContactMessage $contact_message): RedirectResponse
    {
        $contact_message->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'Message deleted.');
    }

    public function destroyAll(): RedirectResponse
    {
        ContactMessage::query()->whereNotNull('read_at')->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', 'All read messages deleted.');
    }
}
