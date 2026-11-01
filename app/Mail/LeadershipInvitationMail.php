<?php

namespace App\Mail;

use App\Models\LeadershipInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The single-use acceptance link for a leadership invitation.
 */
class LeadershipInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public LeadershipInvitation $invitation,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'DARUSO leadership invitation',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.leadership-invitation',
            with: [
                'invitation' => $this->invitation,
                'url' => $this->url,
            ],
        );
    }
}
