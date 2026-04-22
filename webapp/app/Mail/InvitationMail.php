<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation) {}

    public function build(): self
    {
        $fromAddress = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS', 'event.ancs@defensy.io');
        $fromName = config('mail.from.name') ?: env('MAIL_FROM_NAME', config('app.name', 'Carthage Shield'));

        return $this
            ->from($fromAddress, (string) $fromName)
            ->subject("You're Invited to Carthage Shield - Cyber Breach Exercise")
            ->view('emails.invitation', [
                'inviteUrl' => url('/invite/' . $this->invitation->token),
                'invitation' => $this->invitation,
                'expiresAt' => $this->invitation->expires_at?->format('F j, Y'),
            ]);
    }
}
