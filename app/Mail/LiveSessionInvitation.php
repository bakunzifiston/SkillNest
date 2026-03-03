<?php

namespace App\Mail;

use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LiveSessionInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LiveSession $liveSession,
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re invited: ' . $this->liveSession->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.live-session-invitation',
        );
    }
}
