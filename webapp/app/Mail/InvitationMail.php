<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎓 You\'re Invited to RAISEGUARD Academy — AI-Powered Cyber Defence',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'inviteUrl'  => url('/invite/' . $this->invitation->token),
                'invitation' => $this->invitation,
                'expiresAt'  => $this->invitation->expires_at?->format('F j, Y'),
            ]
        );
    }
}
