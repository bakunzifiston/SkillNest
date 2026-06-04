<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::orderByDesc('created_at')->paginate(20);
        return view('admin.contact-messages.index', compact('messages'));
    }

    public function show(ContactMessage $message): View
    {
        if (!$message->read_at) {
            $message->update(['read_at' => now()]);
        }
        return view('admin.contact-messages.show', compact('message'));
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();
        return redirect()->route('admin.contact-messages.index')->with('success', 'Message deleted.');
    }

    public function destroyAll(): RedirectResponse
    {
        ContactMessage::whereNotNull('read_at')->delete();
        return redirect()->route('admin.contact-messages.index')->with('success', 'All read messages deleted.');
    }
}
